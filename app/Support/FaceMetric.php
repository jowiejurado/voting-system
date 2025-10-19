<?php

namespace App\Support;

class FaceMetric
{
	public static function euclidean(array $a, array $b): float
	{
		$sum = 0.0;
		$n = min(count($a), count($b));
		for ($i = 0; $i < $n; $i++) {
			$d = ((float)$a[$i]) - ((float)$b[$i]);
			$sum += $d * $d;
		}
		return sqrt($sum);
	}

	public static function cosine(array $a, array $b): float
	{
		$dot = 0.0;
		$na = 0.0;
		$nb = 0.0;
		$n = min(count($a), count($b));
		for ($i = 0; $i < $n; $i++) {
			$ai = (float)$a[$i];
			$bi = (float)$b[$i];
			$dot += $ai * $bi;
			$na += $ai * $ai;
			$nb += $bi * $bi;
		}
		if ($na == 0 || $nb == 0) return 0.0;
		return $dot / (sqrt($na) * sqrt($nb));
	}
}
