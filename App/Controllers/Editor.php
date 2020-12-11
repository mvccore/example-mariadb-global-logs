<?php

namespace App\Controllers;

class Editor extends \App\Controllers\Base
{
	public function IndexAction () {
		$idGeneralLog = $this->GetParam('idGeneralLog', 'a-zA-Z0-9');
		$lineBegin = $this->GetParam('lineBegin', '0-9', 1, 'int');
		$lineEnd = $this->GetParam('lineEnd', '0-9', 1, 'int');
		$linesRange = $this->GetParam('linesRange', '0-9', 100, 'int');
		
		$generalLog = \App\Models\LogFile::GetById($idGeneralLog);
		
		$lineBegin++;
		$lineEnd++;

		$beginLine = $lineBegin > $linesRange ? $lineBegin - $linesRange : 1;
		$endLine = $lineEnd + $linesRange;
		$currentLine = 1;
		$lines = [];
		$handle = fopen($generalLog->GetFullPath(), 'r+');
		while ($lineStr = fgets($handle)) {
			if ($currentLine >= $beginLine) {
				if ($currentLine > $endLine) break;
				$lines[$currentLine] = $lineStr;
			}
			$currentLine++;
		}
		fclose($handle);

		$this->view->lines = $lines;
		$this->view->lineBegin = $lineBegin;
		$this->view->lineEnd = $lineEnd;
		$this->view->title = $beginLine . '-' . $lineEnd . ' - ' . $generalLog->GetFileName();
	}
}