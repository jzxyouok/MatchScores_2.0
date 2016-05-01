<?php
/**
 * @param $arrData 二维数组
 * @param $saveFile 保存的文件名
 * @param bool $overWriteExistFile 是否覆盖已存在的文件
 * @throws PHPExcel_Exception
 */
function arrayToExcel($arrData, $saveFile, $overWriteExistFile = false) {
	$objExcel = new PHPExcel();
	$objSheet = $objExcel->getActiveSheet();
	$objSheet->fromArray($arrData);
	$w = new \PHPExcel_Writer_Excel2007($objExcel);
	if (!$overWriteExistFile && file_exists($saveFile)) {
		exit($saveFile.' 文件已经存在，不能覆盖生成！');
	}
	$w->save($saveFile);
}

function utf8($s) {
	if (is_string($s)) {
		return mb_convert_encoding($s, 'utf-8', 'gbk');
	} elseif (is_array($s)) {
		foreach ($s as $k=>$v) {
			$s[$k] = mb_convert_encoding($v, 'utf-8', 'gbk');
		}
		return $s;
	}
}

function gbk($s) {
	if (is_string($s)) {
		return mb_convert_encoding($s, 'GBK', 'UTF-8');
	} elseif (is_array($s)) {
		foreach ($s as $k=>$v) {
			$s[$k] = mb_convert_encoding($v, 'GBK', 'UTF-8');
		}
		return $s;
	}
}

function pp($s) {
	echo '<pre>';
	print_r($s);
	echo '</pre>';
}

function pd($s) {
	pp($s);
	die;
}

function copedir( $source,$target ) {
	if(is_dir($source)){
		mkdir($target);
		$dir = dir($source);
		while ( ($f=$dir->read())!==false && $f!='.' && $f!='..' ){
			if (is_dir($source.'/'.$f)){
				copedir($source.'/'.$f,$target.'/'.$f);
			}else{
				copy($source.'/'.$f,$target.'/'.$f);
			}
		}
	}else{
		copy($source,$target);
	}
}

function getFilesListInDir($dir) {
	$hnd = opendir($dir);
	$r=null;
	while (($file = readdir($hnd)) !== false)
	{
		if (!in_array($file, ['.', '..'])) {
			$r[]=$file;
		}
	}
	closedir($hnd);
	return $r;
}

function configFilePath($filename)
{
	return base_path("配置文件/$filename");
}

function matchConfig($strKeys) {
	$keys = explode('.', $strKeys);
	$matchConfig = \App\Modules\MatchConfig\Config::read();
	$r=null;
	foreach ($keys as $key) {
		if (!$r) {
			if (!isset($matchConfig[$key])) {
				dump($strKeys);
//				pd("{$strKeys}：{$key} 不存在");
			}

			$r = $matchConfig[$key];
		} else {
			$r = $r[$key];
		}
	}
	return $r;
} 