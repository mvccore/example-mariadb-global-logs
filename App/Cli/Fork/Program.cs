using System;
using System.IO;
using System.Diagnostics;
using System.Collections.Generic;
using System.Text;

namespace Fork {
	class Program {
		private static List<string> _executableExtensions = new List<string> { "exe", "com", "bat", "cmd" };
		public static void Main(string[] args) {
#if DEBUG
			//args = new string[] { "php.exe", "test.php", "a", "b", "c" };
#endif
			if (args.Length == 0) return;

			// check if first arg is any executable and try to absolutize it by %PATH% if necessary:
			var executableFileName = args[0];
			var isFirstArgPathOrNonExecutable = Program._isPathOrNonExecutable(executableFileName);
			if (!isFirstArgPathOrNonExecutable) {
				var absolutizedExecutable = Program._tryToAbsolutizeExecutable(executableFileName);
				if (!String.IsNullOrEmpty(absolutizedExecutable)) {
					args[0] = absolutizedExecutable;
				} else {
#if DEBUG
					Program._debugWrite(
						String.Join("\n\n", new string[]{
							"ARGUMENTS:\n\t" + String.Join(" ", args),
							"SYSTEM PATH VAR:\n\t" + Environment.GetEnvironmentVariable("PATH"),
						})	
					);
#endif
					Console.WriteLine(
						"ERROR: Not possible to absolutize executable `" + executableFileName + "`."
					);
					return;
				}
			}

			// complete whole arguments string:
			var argsStr = String.Join(" ", args);
#if DEBUG
			// write whole CLI command and %PATH% variable into log file:
			Program._debugWrite(
				String.Join("\n\n", new string[]{
					"COMMAND TO RUN:\n\tcmd.exe /C " + argsStr,
					"SYSTEM PATH VAR:\n\t" + Environment.GetEnvironmentVariable("PATH"),
				})	
			);
#endif
			// run another process without waiting for response:
			Process process = new Process();
			ProcessStartInfo startInfo = new ProcessStartInfo();
			startInfo.WindowStyle = ProcessWindowStyle.Hidden;
			startInfo.FileName = "cmd.exe";
			startInfo.Arguments = "/C " + argsStr;
			process.StartInfo = startInfo;
			process.Start();

			Console.WriteLine(1);
		}

		private static bool _isPathOrNonExecutable (string arg) {
			var hasAnyDirSeparator = arg.Replace('/', '\\').IndexOf('\\') != -1;
			if (hasAnyDirSeparator) return true;
			var lastDotPos = arg.LastIndexOf('.');
			var hasExtension = lastDotPos != -1;
			var hasExecutableExtension = false;
			if (hasExtension) {
				var extension = arg.Substring(lastDotPos + 1).ToLower();
				hasExecutableExtension = Program._executableExtensions.IndexOf(extension) != -1;
			}
			if (!hasExtension || hasExecutableExtension) return false;
			return true;
		}

		private static string _tryToAbsolutizeExecutable (string executableFileName) {
			var rawPath = System.Environment.GetEnvironmentVariable("PATH");
			var paths = rawPath.Split(';');
			string fullPath;
			var absolutized = false;
			foreach (var path in paths) {
				fullPath = path.Replace('/', '\\').TrimEnd('\\') + '\\' + executableFileName;
				if (File.Exists(fullPath)) {
					if (fullPath.IndexOf(' ') != -1)
						fullPath = @"""" + fullPath + @"""";
					executableFileName = fullPath;
					absolutized = true;
					break;
				}
			}
			if (!absolutized) return null;
			return executableFileName;
		}
#if DEBUG
		private static void _debugWrite (string msg) {
			var currentDir = System.Reflection.Assembly.GetEntryAssembly().Location;
			var lastDirSeparatorPos = currentDir.LastIndexOf('\\');
			if (lastDirSeparatorPos != -1)
				currentDir = currentDir.Substring(0, lastDirSeparatorPos);
			var debugLogFullPath = currentDir + "\\Fork.debug";
			if (!File.Exists(debugLogFullPath)) {
				FileStream writer = File.Create(debugLogFullPath);
				var bytes = Encoding.UTF8.GetBytes(msg + "\n\n");
				writer.Write(bytes, 0, bytes.Length);
				writer.Close();
			} else { 
				TextWriter writer = new StreamWriter(debugLogFullPath, true);
				writer.WriteLine(msg + "\n\n");
				writer.Close(); 
			}
		}
#endif
	}
}
