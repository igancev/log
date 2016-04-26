<?php

namespace Igancev\Log;

/**
 * Класс для файлового логирования
 * Class for logging into the file
 *
 * @version 1.0.2
 * @namespace  Igancev\Log
 * @package    log
 * @author     Ivan Gantsev <ivangancev@yandex.ru>
 * @copyright  2016 Ivan Gantsev
 * @link       http://github.com/igancev/log
 *
 */
class Log
{
	/**
	 * Относительный путь директории расположения файла
	 * The relative path of the directory location of the file format
	 * @example "/log/any/path/to/file/"
	 * @var string
	 */
	private $fileDir;

	/**
	 * Имя файла
	 * File name
	 * @var string
	 */
	private $fileName;

	/**
	 * Расширение файла
	 * File extension
	 * @var string
	 */
	private $fileExt;

	/**
	 * Конечный полный путь к файлу
	 * Final full path to the file
	 * @var string
	 */
	private $filePath;

	/**
	 * Текст сообщения лога
	 * Log text message
	 * @var string
	 */
	private $message;

	/**
	 * Направление сортировки записи записи в файл
	 * The sorting order of the records written to the file
	 * @var string
	 */
	private $sort;

	/**
	 * Метка о времени жизни файла
	 * The label about the lifetime of the file
	 * @var int
	 */
	private $lifeTime;

	/**
	 * Метка дебаг режима
	 * Label debug mode
	 * @var bool
	 */
	private $debug;

	/**
	 * Константа сотрировки ASC
	 */
	const SORT_ASC = 'asc';
	
	/**
	 * Константа сотрировки DESC
	 */
	const SORT_DESC = 'desc';

	/**
	 * Конструктор/Constructor
	 * Инициализация параметров по умолчанию и необязательная установка текста лога
	 * Init default params and optional set text log
	 *
	 * @param $text
	 */
	public function __construct($text = '')
	{
		$this->setFileDir('/log/');
		$this->setFileName('log');
		$this->setFileExt('txt');
		$this->setSort(self::SORT_ASC);
		$this->setMessage($text);
		$this->setDebug(false);
	}

	/**
	 * Добавление записи в файл лога c учетом текущей сортировки. Создание файла, если отсутствует
	 * Add record in log file based on the current sorting. Creating file, if exist
	 */
	public function addLog()
	{
		$this->setFilePath();
		$this->message .= "\n";
		$currentLog = '';

		if (file_exists($this->filePath))
		{
			$currentLog = file_get_contents($this->filePath);
		}

		if ($this->sort == 'desc')
		{
			$newLog = $this->message . $currentLog;
		}
		else
		{
			$newLog = $currentLog . $this->message;
		}
		
		// print on debug mode
		if($this->debug)
		{
			echo $this->message;
		}

		file_put_contents($this->filePath, $newLog);
	}

	/**
	 * Установка директории файла лога, создание при ее отсутствии
	 * Set directory log file, create by miss
	 *
	 * @param $fileDir string
	 *
	 * @return $this
	 */
	public function setFileDir($fileDir)
	{
		if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $fileDir))
		{
			$arDirs = explode('/', $fileDir);

			$arDirs = array_diff($arDirs, ['']);

			$chainDir = $_SERVER['DOCUMENT_ROOT'] . '/';
			foreach ($arDirs as $dirName)
			{
				$chainDir .= $dirName . '/';
				if (!file_exists($chainDir))
				{
					mkdir($chainDir);
				}
			}
		}

		$this->fileDir = $fileDir;

		return $this;
	}

	/**
	 * Добавление куска лога к общему сообщению
	 * Add part of log to message
	 *
	 * @param $text
	 *
	 * @return $this
	 */
	public function addText($text)
	{
		$this->message .= $text;

		return $this;
	}
	
	/**
	 * Установка имени файла
	 * Set file name
	 *
	 * @param $fileName string
	 *
	 * @return $this
	 */
	public function setFileName($fileName)
	{
		$this->fileName = $fileName;

		return $this;
	}

	/**
	 * Установка расширения файла (по умолчанию txt)
	 * Set file extension (txt default)
	 *
	 * @param $fileExt string
	 *
	 * @return $this
	 */
	public function setFileExt($fileExt)
	{
		$this->fileExt = $fileExt;

		return $this;
	}

	/**
	 * Установка сортировки записи значений лога в файле
	 * Set values sorting log entries to a file
	 *
	 * @param $fileSort string (asc/desc)
	 *
	 * @return $this
	 */
	public function setSort($fileSort)
	{
		$this->sort = $fileSort;

		return $this;
	}

	/**
	 * Установка времени жизни лога в днях
	 * Set log life time in days
	 *
	 * @param $lifeTimeDays int
	 *
	 * @return $this
	 */
	public function setLifeTime($lifeTimeDays)
	{
		if (!empty($lifeTimeDays) && is_numeric($lifeTimeDays))
		{
			$this->lifeTime = $lifeTimeDays;
		}

		return $this;
	}

	/**
	 * @param bool $debug
	 * 
	 * @return $this
	 */
	public function setDebug($debug)
	{
		$this->debug = $debug;
		return $this;
	}

	/**
	 * Формирование полного пути к файлу лога
	 * Forming full path to log file
	 */
	private function setFilePath()
	{
		$this->filePath = $_SERVER['DOCUMENT_ROOT'] . $this->fileDir . $this->fileName . '-' . date('d.m.Y');

		if (!empty($this->lifeTime))
		{
			$this->filePath .= '-lifetime-' . $this->lifeTime;
		}

		$this->filePath .= '.' . $this->fileExt;
	}

	/**
	 * Формирование шаблона сообщения
	 * Forming template message
	 *
	 * @param $text string
	 */
	protected function setMessage($text)
	{
		$this->message = date('H:i:s') . ' ' . $text . ' ';
	}
}