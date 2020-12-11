<?php

namespace Libs;

class Sql
{
	/**
	 * @param string $str 
	 * @return \string[]
	 */
	public static function Explode (& $str) {
		if (mb_strpos($str, ';') === FALSE) return [$str];
		$parts = [];
		$offset = 0;
		$pos = 0;
		$startPos = 0;
		$endPos = 0;
		$len = mb_strlen($str);
		
		$inactiveSections = self::getSemicolonInsideStringsPositions($str, $len);
		$inactiveSectionsCount = count($inactiveSections);
		$inactiveSectionsDetected = $inactiveSectionsCount > 0;
		$inactiveSectionIndex = 0;
		
		$counter = 0;
		while ($offset < $len && $counter++ < 100) {
			$pos = mb_strpos($str, ';', $offset);
			
			// If there is no other semicolon, 
			// push the rest of the string and break
			if ($pos === FALSE) {
				if ($startPos < $len) 
					$parts[] = mb_substr($str, $startPos);
				break;
			}
			
			$endPos = $pos + 1;
			
			// Check if semicolon is inside any string and skip it:
			$semicolonInsideString = FALSE;
			if ($inactiveSectionsDetected) {
				for ($i = $inactiveSectionIndex; $i < $inactiveSectionsCount; $i++) {
					list($inactiveBegin, $inactiveEnd) = $inactiveSections[$i];
					if ($inactiveEnd < $pos) {
						$inactiveSectionIndex++;
						continue;
					}
					if ($inactiveBegin > $pos) {
						break;
					}
					if ($inactiveBegin < $pos && $inactiveEnd > $pos) {
						$semicolonInsideString = TRUE;
						$offset = $inactiveEnd + 1;
						break;
					}
				}
			}
			// Move to next loop:
			if ($semicolonInsideString) continue;
			
			// Try to get substring from offset to semicolon + one
			// and add it into result parts if it is not empty string:
			$parts[] = mb_substr($str, $startPos, $endPos - $startPos);
			
			// Move to next loop:
			$startPos = $endPos;
			$offset = $endPos;
		}
		return $parts;
	}

	/**
	 * @param string $str 
	 * @return string
	 */
	public static function RemoveComments (& $str) {
		if (mb_strpos($str, '/*') === FALSE) return $str;

		$parts = [];
		$offset = 0;
		$pos = 0;
		$startPos = 0;
		$commentStartPos = 0;
		$commentEndPos = 0;
		$len = mb_strlen($str);
		
		$inactiveSections = self::getSemicolonInsideStringsPositions($str, $len);
		$inactiveSectionsCount = count($inactiveSections);
		$inactiveSectionsDetected = $inactiveSectionsCount > 0;
		$inactiveSectionIndex = 0;
		
		$counter = 0;
		while ($offset < $len && $counter++ < 100) {
			$pos = mb_strpos($str, '/*', $offset);
			
			// If there is no other comment opening, break
			if ($pos === FALSE) {
				if ($startPos < $len) 
					$parts[] = mb_substr($str, $startPos);
				break;
			}
			
			// Check if semicolon is inside any string and skip it:
			if (self::removeCommentsIsInsideString (
				$inactiveSections, $offset, $pos, 
				$inactiveSectionsDetected, $inactiveSectionIndex, $inactiveSectionsCount
			)) continue;
			
			$commentStartPos = $pos;
			
			$pos = mb_strpos($str, '*/', $offset);
			
			// If there is no other comment closing, break
			if ($pos === FALSE) {
				if ($startPos < $len) 
					$parts[] = mb_substr($str, $startPos);
				break;
			}
			
			// Check if semicolon is inside any string and skip it:
			if (self::removeCommentsIsInsideString (
				$inactiveSections, $offset, $pos, 
				$inactiveSectionsDetected, $inactiveSectionIndex, $inactiveSectionsCount
			)) continue;
			
			$commentEndPos = $pos + 2;
			
			// Try to get substring from offset to semicolon + one
			// and add it into result parts if it is not empty string:
			$parts[] = mb_substr($str, $startPos, $commentStartPos - $startPos);
			
			// Move to next loop:
			$startPos = $commentEndPos;
			$offset = $commentEndPos;
		}
		return implode('', $parts);
	}
	
	protected static function removeCommentsIsInsideString (& $inactiveSections, & $offset, $pos, $inactiveSectionsDetected, $inactiveSectionIndex, $inactiveSectionsCount) {
		if (!$inactiveSectionsDetected) return FALSE;
		$commentInsideString = FALSE;
		for ($i = $inactiveSectionIndex; $i < $inactiveSectionsCount; $i++) {
			list($inactiveBegin, $inactiveEnd) = $inactiveSections[$i];
			if ($inactiveEnd < $pos) {
				$inactiveSectionIndex++;
				continue;
			}
			if ($inactiveBegin > $pos) break;
			if ($inactiveBegin < $pos && $inactiveEnd > $pos) {
				$commentInsideString = TRUE;
				$offset = $inactiveEnd + 1;
				break;
			}
		}
		return $commentInsideString;
	}

	protected static function getSemicolonInsideStringsPositions (& $str, $len) {
		$sections = [];
		$quotes = [];
		
		$offset = 0;
		$quotPos = 0;
		while ($offset < $len) {
			$quotPos = mb_strpos($str, "'", $offset);
			if ($quotPos === FALSE) break;
			
			// try to count any previous back slashes:
			$slashesCnt = 0;
			$prevCharPos = $quotPos - 1;
			while (TRUE) {
				$prevChar = mb_substr($str, $prevCharPos, 1);
				if ($prevChar !== '\\') break;
				$slashesCnt++;
				$prevCharPos -= 1;
			}
			if ($slashesCnt > 0 && $slashesCnt % 2 === 1) {
				// escaped quot:
				$offset = $quotPos + 1;
				continue;
			}
			
			// add quote position:
			$quotes[] = $quotPos;
			$offset = $quotPos + 1;
		}
		
		$quotesCount = count($quotes);
		if ($quotesCount % 2 === 1) throw new \Exception(
			"String escape detection error: \n" . $str
		);
		
		for ($i = 0; $i < $quotesCount; $i += 2) {
			$sections[] = [
				$quotes[$i], $quotes[$i + 1]
			];
		}
		
		return $sections;
	}
}