<?php namespace App\Modules\Excel;

use App\Modules\MatchConfig\Item;
use Illuminate\Support\Facades\Cache;

/**
* 根据配置信息（比赛的EXCEL配置文件），生成EXCEL模板文件
*/
class Template extends Base {


	/**
	 * 主要用来生成裁判用表
	 * @param $参数表表名 Excel配置文件里的表格名
	 * @param $保存文件名
	 * @return \PHPExcel
	 * @throws \PHPExcel_Exception
	 */
	public static function 根据参数生成表格($参数表表名, $保存文件名)
	{
		$matchConfig = \App\Modules\MatchConfig\Config::read();
//		pd($matchConfig);///////////////////////////////////////////////
		$objExcel = \PHPExcel_IOFactory::load(gbk(configFilePath(Cache::get('配置文件'))));
		//隐藏所有已有表格，模板也被隐藏掉了，需要判断一下并取消隐藏
		foreach ($objExcel->getWorksheetIterator() as $_sheet) {
			$_sheet->setSheetState(\PHPExcel_Worksheet::SHEETSTATE_HIDDEN);
		}
		//复制“裁判用表”，用于复制格式
		$objSheetConfig = $objExcel->getSheetByName($参数表表名);

		foreach ($matchConfig[$参数表表名] as $item=>$itemConfig) {
			//检查表格在配置文件中是否存在，如果存在就该表取消隐藏，否则新建一个，手工做的模板表表名命名规则：$参数表表名 + 配置里的表名（例：裁判用表A1）
			$objSheet = $objExcel->getSheetByName($参数表表名 . $itemConfig['表名']);
			if ($objSheet != null) {
				$objSheet->setSheetState(\PHPExcel_Worksheet::SHEETSTATE_VISIBLE);
				$objSheet->setTitle($itemConfig['表名']);	//改名，去掉标识类别的前缀
				$objSheet->getTabColor()->setRGB('000000');	//设置标签颜色
			} else {
				$objSheet = $objExcel->createSheet();
				$objSheet->setTitle($itemConfig['表名']);	//设置表名
				$objSheet->getTabColor()->setRGB('000000');	//设置标签颜色
				//设置标题行（第一行）、首条数据行（第二行）
//				for ($i = 0; $i < count($itemConfig['字段']); $i++) {
				//列索引（第几个列）
				$n = 0;
				foreach ($itemConfig['字段'] as $k => $_val) {
//					dd($itemConfig);
					////////标题（第一行）////////
					$colName = \PHPExcel_Cell::stringFromColumnIndex($n);//列名
					$objSheet->getRowDimension(1)->setRowHeight($itemConfig['标题行高']);
					$objSheet->getColumnDimension($colName)->setWidth($itemConfig['字段宽度'][$k]);//设置列宽
					$objCell = $objSheet->getCellByColumnAndRow($n, 1);//单元格对象
					$objCell->setValueExplicit($itemConfig['字段'][$k], \PHPExcel_Cell_DataType::TYPE_STRING);//填值
					//设置格式
					$objCell->getStyle()->applyFromArray(Style::$标题);

					///////数据行（第二行）/////////
					$objSheet->getRowDimension(2)->setRowHeight($itemConfig['数据行高']);//设置行高
					$objCell = $objSheet->getCellByColumnAndRow($n, 2);//单元格对象
					$cellName = $colName.'2';//单元格名称
					if (isset($itemConfig['字段值'][$k])) {
						//设置值
						$objCell->setValueExplicit($itemConfig['字段值'][$k], \PHPExcel_Cell_DataType::TYPE_STRING);
						//复制格式
						$cellStyle = $objSheetConfig->getCell($itemConfig['字段格式'][$k])->getStyle();
						$objSheet->duplicateStyle($cellStyle, "$cellName:$cellName");
					}
					//设置格式
					$objCell->getStyle()->applyFromArray(Style::$细边框);

					$n++;
				}
			}
		}

		//save
		$objWriter = new \PHPExcel_Writer_Excel5($objExcel);
		$objWriter->save($保存文件名);

		return $objExcel;
	}

	/**
	 * 主要代码复制至“根据参数生成表格”方法
	 * @param $参数表表名 Excel配置文件里的表格名
	 * @param $保存文件名
	 * @return \PHPExcel
	 * @throws \PHPExcel_Exception
	 */
	public static function 生成成绩册模板()
	{
		$matchConfig = \App\Modules\MatchConfig\Config::read();
		$objExcel = \PHPExcel_IOFactory::load(gbk(configFilePath(Cache::get('配置文件'))));
		//隐藏所有已有表格，模板也被隐藏掉了，需要判断一下并取消隐藏
		foreach ($objExcel->getWorksheetIterator() as $_sheet) {
			$_sheet->setSheetState(\PHPExcel_Worksheet::SHEETSTATE_HIDDEN);
		}
		//复制“成绩册”，用于复制格式
		$objSheetConfig = $objExcel->getSheetByName('成绩册');

		foreach ($matchConfig['成绩册'] as $item=>$itemConfig) {
//			dump($itemConfig);
			//检查表格在配置文件中是否存在，如果存在就该表取消隐藏，否则新建一个，手工做的模板表表名命名规则：$参数表表名 + 配置里的表名（例：裁判用表A1）
			$objSheet = $objExcel->getSheetByName('成绩册' . $itemConfig['表名']);
			if ($objSheet != null) {
				$objSheet->setSheetState(\PHPExcel_Worksheet::SHEETSTATE_VISIBLE);
				$objSheet->setTitle($itemConfig['表名']);	//改名，去掉标识类别的前缀
				$objSheet->getTabColor()->setRGB('000000');	//设置标签颜色
			} else {
				$objSheet = $objExcel->createSheet();
				$objSheet->setTitle($itemConfig['表名']);	//设置表名
				$objSheet->getTabColor()->setRGB('000000');	//设置标签颜色
				//设置标题行（第一行）、首条数据行（第二行）
				//列索引（第几个列）
				$colIndex = 0;
				foreach ($itemConfig['字段'] as $k => $_val) {
//					dd($itemConfig);
					////////标题（第2行，第1行是项目名称、级别）////////
					$colName = \PHPExcel_Cell::stringFromColumnIndex($colIndex);//列名
					$objSheet->getRowDimension(2)->setRowHeight($itemConfig['标题行高']);
					$objSheet->getColumnDimension($colName)->setWidth($itemConfig['字段宽度'][$k]);//设置列宽
					$objCell = $objSheet->getCellByColumnAndRow($colIndex, 2);//单元格对象
					$objCell->setValueExplicit($itemConfig['字段'][$k], \PHPExcel_Cell_DataType::TYPE_STRING);//填值
					//设置格式
					$objCell->getStyle()->applyFromArray(Style::$成绩册_标题);

					///////数据行（第3行）/////////
					$objSheet->getRowDimension(3)->setRowHeight($itemConfig['数据行高']);//设置行高
					$objCell = $objSheet->getCellByColumnAndRow($colIndex, 3);//单元格对象
					$cellName = $colName.'3';//单元格名称
					if (isset($itemConfig['字段值'][$k])) {
						//设置值
						$objCell->setValueExplicit($itemConfig['字段值'][$k], \PHPExcel_Cell_DataType::TYPE_STRING);
						//复制格式
						$cellStyle = $objSheetConfig->getCell($itemConfig['字段格式'][$k])->getStyle();
						$objSheet->duplicateStyle($cellStyle, "$cellName:$cellName");
					}
					//设置格式
					$objCell->getStyle()->applyFromArray(Style::$细边框);

					$colIndex++;
				}
				//设置项目名称（A1）、格式
				$A1 = $objSheet->getCell('A1');
				$A1->setValue($colIndex-1);//将最大列号的索引值保存在此，以方便填充数据时确定组别单元格的位置
				$A1->getStyle()->applyFromArray(Style::$成绩册_项目名称);
				$A1->getStyle()->applyFromArray(Style::$左对齐);
				$objSheet->getRowDimension(1)->setRowHeight(30);	//行高


				//设置组别
				$Ax = $objSheet->getCellByColumnAndRow($colIndex-1, 1);
				$Ax->setValue('小学组');
				$Ax->getStyle()->applyFromArray(Style::$成绩册_项目名称);
				$Ax->getStyle()->applyFromArray(Style::$右对齐);
			}
		}

		//save
		$objWriter = new \PHPExcel_Writer_Excel5($objExcel);
		$objWriter->save(gbk(matchConfig('全局.工作目录').'/模板/成绩册模板.xls'));

		return $objExcel;
	}

	public static function 生成裁判用表模板bak($saveFile = null)
	{
		$matchConfig = \App\Modules\MatchConfig\Config::read();
//		pd($matchConfig);///////////////////////////////////////////////
		$objExcel = \PHPExcel_IOFactory::load(gbk(configFilePath(Cache::get('配置文件'))));
		//隐藏所有已有表格，模板也被隐藏掉了，需要判断一下并取消隐藏
		foreach ($objExcel->getWorksheetIterator() as $_sheet) {
			$_sheet->setSheetState(\PHPExcel_Worksheet::SHEETSTATE_HIDDEN);
		}
		//复制“裁判用表”，用于复制格式
		$objSheetConfig = $objExcel->getSheetByName('裁判用表');

		foreach ($matchConfig['裁判用表'] as $item=>$itemConfig) {
//			检查表格在配置文件中是否存在，如果存在就该表取消隐藏，否则新建一个
			$objSheet = $objExcel->getSheetByName($itemConfig['表名']);
			if ($objSheet != null) {
				$objSheet->setSheetState(\PHPExcel_Worksheet::SHEETSTATE_VISIBLE);
			} else {
				$objSheet = $objExcel->createSheet();
				$objSheet->setTitle($itemConfig['表名']);	//设置表名
				$objSheet->getTabColor()->setRGB('000000');	//设置标签颜色
				//设置标题行（第一行）、首条数据行（第二行）
//				for ($i = 0; $i < count($itemConfig['字段']); $i++) {
				//列索引（第几个列）
				$n = 0;
				foreach ($itemConfig['字段'] as $k => $_val) {
//					dd($itemConfig);
					////////标题（第一行）////////
					$colName = \PHPExcel_Cell::stringFromColumnIndex($n);//列名
					$objSheet->getRowDimension(1)->setRowHeight($itemConfig['标题行高']);
					$objSheet->getColumnDimension($colName)->setWidth($itemConfig['字段宽度'][$k]);//设置列宽
					$objCell = $objSheet->getCellByColumnAndRow($n, 1);//单元格对象
					$objCell->setValueExplicit($itemConfig['字段'][$k], \PHPExcel_Cell_DataType::TYPE_STRING);//填值
					//设置格式
					$objCell->getStyle()->applyFromArray(Style::$标题);

					///////数据行（第二行）/////////
					$objSheet->getRowDimension(2)->setRowHeight($itemConfig['数据行高']);//设置行高
					$objCell = $objSheet->getCellByColumnAndRow($n, 2);//单元格对象
					$cellName = $colName.'2';//单元格名称
					if (isset($itemConfig['字段值'][$k])) {
						//设置值
						$objCell->setValueExplicit($itemConfig['字段值'][$k], \PHPExcel_Cell_DataType::TYPE_STRING);
						//复制格式
						$cellStyle = $objSheetConfig->getCell($itemConfig['字段格式'][$k])->getStyle();
						$objSheet->duplicateStyle($cellStyle, "$cellName:$cellName");
					}
					//设置格式
					$objCell->getStyle()->applyFromArray(Style::$细边框);

					$n++;
				}
			}
		}

		//save
		if ($saveFile!=null) {
			$objWriter = new \PHPExcel_Writer_Excel5($objExcel);
			$objWriter->save($saveFile);
		}

		return $objExcel;
	}


	public static function 生成成绩录入表模板($saveFile = null)
	{
		$objExcel = \PHPExcel_IOFactory::load(gbk(base_path('通用模板/成绩录入.xls')));
		$objSheet = $objExcel->getSheetByName('模板');

		$items = matchConfig('项目');
		foreach ($items as $itemName => $item) {
			$objItem = new Item($itemName);
			$newSheet = clone $objSheet;
			$newSheet->setTitle($objItem->表名);
			$newSheet->getTabColor()->setRGB('000000');	//设置标签颜色
			$newSheet->setCellValue('A1', $itemName);//设置项目名称
			$objExcel->addSheet($newSheet);
		}
		//隐藏模板表
		$objSheet->setSheetState(\PHPExcel_Worksheet::SHEETSTATE_HIDDEN);
		//save
		if ($saveFile!=null) {
			$objWriter = new \PHPExcel_Writer_Excel5($objExcel);
			$objWriter->save($saveFile);
		}
		return $objExcel;
	}


}