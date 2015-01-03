<?php
class MyDate {
	public static function relativeDatetime($datetimeStr) {
		$datetime = new DateTime ( $datetimeStr );
		$datetimeNow = new DateTime ( 'now' );
		$i = $datetime->diff ( $datetimeNow );
		if ($i->y)
			return $i->y . "年前";
		if ($i->m)
			return $i->m . "月前";
		if ($i->d)
			return $i->d . "日前";
		if ($i->h)
			return $i->h . "時間前";
		if ($i->i)
			return $i->i . "分前";
		return $i->s . "秒前";
	}
}