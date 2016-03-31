<?php namespace App\Modules\Score;

use App\User;
use App\Modules\MatchConfig\Score;

class Calc
{
	/**
	 * 将数据库中的“成绩排名”字段计算、填充
	 * @param $项目名称
	 */
	public function 填充排序字段($项目名称)
	{
		$cfgScore	= new Score($项目名称);
		////////////计算填充成绩排序字段的内容////////////
		//清空原有数据
		\DB::update("update users set 成绩排序='' where 项目=?", [$项目名称]);
		$sortFun = $cfgScore->成绩排序;
		$rs = User::where('项目', $项目名称)->get();
		foreach ($rs as $row) {
			if (strlen($row->原始成绩)) {
				$rawScores = unserialize($row->原始成绩);
				$row->成绩排序 = Sort::$sortFun($rawScores[0],$rawScores[1]);
				$row->save();
			}
		}
	}


	/**
	 * @param $item 项目
	 * @param $group 组别
	 * @param string $orderType 排序方式：升序、降序
	 */
	public function 单项排名($item, $group, $orderType = '降序')
	{
		//先清空原来排名
		\DB::update("update users set 排名='' where 项目=? and 组别=?", [$item, $group]);

		$orderBy = $orderType == '降序' ? 'desc' : 'asc';

		$users = User::where('项目', $item)
			->where('组别', $group)
			->where('成绩排序', '!=', '')
			->orderBy('成绩排序', $orderBy)
			->get();
		//上一个排名
		$lastRank = 0;
		//上一个成绩（最后生成用于排名的）
		$lastScore = '';
		foreach ($users as $user) {
			if ($lastRank == 0) { //第一条数据
				$rank = 1;
				$user->排名 = $rank;
				$user->save();

				$lastRank = $rank;
				$lastScore = $user->成绩排序;
			} else { //第二条到最后一条数据
				if ($lastScore == $user->成绩排序) { //并列
					$rank = $lastRank;
				} else {
					$rank = $lastRank + 1;
				}
				$user->排名 = $rank;
				$user->save();

				$lastRank = $rank;
				$lastScore = $user->成绩排序;
			}
		} // foreach users as user

	} //排名
	
	/**
	 * 根据比例设定各个组的奖项，要先计算出排名才能使用该方法
	 * @param $item 项目
	 * @param $group 组别
	 * @param $jiangxiangAndBili 奖项及比例 按从高到底排列 例（一、二、三等奖比例分别为10%、20%、30% ）：array('一等奖'=>'0.1', '二等奖'=>'0.2', '三等奖'=>'0.3')
	 */
	public function 奖项($item, $group, $jiangxiangAndBili)
	{
		//清空本项目、本组别的奖项值
		\DB::update("update users set 奖项='' WHERE 项目=? and 组别=?", [$item, $group]);
		//本项目、本组别总人数
		$userCount = User::where('项目', $item)
			->where('组别', $group)
			->where('成绩排序','!=','')
			->count();
		//当某个项目还没有导入任何成绩时，$userCount的值为0,而$thisUserCount至少为1,下面的for循环中读取$users[$i]会超出下标造成错误
		if ($userCount == 0) return;

		foreach ($jiangxiangAndBili as $jiangxiang=>$bili) { //$bili比例，$jiangxiang奖项
			$users = User::whereRaw("项目=? and 组别=? and 奖项='' order by if(排名='',1,0), abs(排名)", [$item, $group])->get();
			$thisUserCount = round($bili * $userCount);//本奖项的人数 四舍五入
			//如果该组总人数特别少（如：3人），一等奖（10%）计算出来的人数可能等于0，这时就要调整为1人
			$thisUserCount = $thisUserCount > 0 ? $thisUserCount : 1;

			for ($i = 0; $i < $thisUserCount; $i++) {
				$user = $users[$i];
				$user->奖项 = $jiangxiang;
				$user->save();
				
				$thisItemGroupUserSort = $user->排名;//本项目、组别、奖项的最后一个用户的排名
			}

			//处理与本奖项最后一人排名相同人的奖项，应该与之相同（并列）。
			\DB::update("update users set 奖项=? WHERE 项目=? and  组别=? and 排名=?",
				[$jiangxiang, $item, $group, $thisItemGroupUserSort]);
		}
	}
	


}//class