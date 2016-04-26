<?php

namespace Igancev\Log;

/**
 * Класс для чистки (удаления) логов
 * Class to clean (delete) the logs
 *
 * @version 1.0.2
 * @namespace  igancev\log
 * @package    log
 * @author     Ivan Gantsev <ivangancev@yandex.ru>
 * @copyright  2016 Ivan Gantsev
 * @link       http://github.com/igancev/log
 *
 */
class LogGarbageCollector
{
	/**
	 * Удаление устаревших файлов
	 * Remove obsolete files
	 *
	 * @param string $relativePath
	 * @param bool $recursive
	 */
	public static function clearOld($relativePath, $recursive = true)
	{
		$arAllFiles = self::getAllFiles($relativePath, $recursive);

		foreach ($arAllFiles as $splFileInfoObject)
		{
			if (!self::checkFileForRelevance($splFileInfoObject->getFileName()))
			{
				// removing file
				$removingFilePath = $splFileInfoObject->getPathName();

				if (is_writable($removingFilePath))
				{
					unlink($removingFilePath);
				}
			}
		}
	}

	/**
	 * Получение массива объектов \SplFileInfo
	 * Get array of object \SplFileInfo
	 *
	 * @param $relativePath
	 * @param bool $recursive
	 *
	 * @return array
	 */
	private static function getAllFiles($relativePath, $recursive = true)
	{
		$arOld = array();

		$dir = $_SERVER['DOCUMENT_ROOT'] . $relativePath;

		// some flags to filter . and .. and follow symlinks
		$flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::KEY_AS_FILENAME;

		if ($recursive)
		{
			// create a simple recursive directory iterator
			$iterator = new \RecursiveDirectoryIterator($dir, $flags);

			// make it a truly recursive iterator
			$iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);
		}
		else
		{
			$iterator = new \DirectoryIterator($dir);
		}

		// iterate over it
		foreach ($iterator as $file)
		{
			if ($file->isDir())
			{
				continue;
			}

			if (method_exists($file, 'isDot'))
			{
				if (!$file->isDot())
				{
					$arOld[] = new \SplFileInfo($file->getPathname());
				}
			}
			else
			{
				$arOld[] = $file;
			}
		}

		return $arOld;
	}

	/**
	 * Проверка файла на актуальность перед удалением по метке lifetime в имени
	 * Check file for relevance before removing by the tag lifetime in the name
	 *
	 * @param $fileName
	 *
	 * @return bool
	 */
	private static function checkFileForRelevance($fileName)
	{
		// by default, the file is relevant
		$relevance = true;

		$fileLifetimeDays = self::getLifetime($fileName);
		$fileCreateDate = self::getCreateDate($fileName);

		if ($fileLifetimeDays != null && $fileCreateDate != null)
		{
			$relevance = self::getRelevance($fileCreateDate, $fileLifetimeDays);
		}

		return $relevance;
	}

	/**
	 * Определение актуальности файла
	 * The definition of relevancy
	 *
	 * @param $createDateStr
	 * @param $lifeTime
	 *
	 * @return bool
	 */
	private static function getRelevance($createDateStr, $lifeTime)
	{
		$createDate = \DateTime::createFromFormat('d.m.Y', $createDateStr);
		$dateInterval = $createDate->diff(\DateTime::createFromFormat('d.m.Y', date('d.m.Y')));
		$lastDays = $dateInterval->d;

		return (int)$lifeTime > (int)$lastDays ? true : false;
	}

	/**
	 * Получаем метку времени жизни из имени файла
	 * Get label of the life of the file name
	 *
	 * @param $fileName
	 *
	 * @return int
	 */
	private static function getLifetime($fileName)
	{
		$lifetimeDays = null;

		$pattern = '/lifetime-([0-9]+)/';
		preg_match($pattern, $fileName, $matches);

		if (!empty($matches[1]))
		{
			$lifetimeDays = $matches[1];
		}

		return $lifetimeDays;
	}

	/**
	 * Получаем дату создания файла из имени файла
	 * Get date create of the file name
	 *
	 * @param $fileName
	 *
	 * @return string
	 */
	private static function getCreateDate($fileName)
	{
		$createDate = null;

		$pattern = '/[aA-zZ]+-([\d]+.[\d]+.[\d]+)/';
		preg_match($pattern, $fileName, $matches);

		if (!empty($matches) && strlen($matches[1]) == 10)
		{
			$createDate = $matches[1];
		}

		return $createDate;
	}
}