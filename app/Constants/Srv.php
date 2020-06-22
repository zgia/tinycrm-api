<?php

declare(strict_types=1);

namespace App\Constants;

// 接口返回成功或者失败
define('I_SUCCESS', 0);
define('I_FAILURE', 1);

/*
 * 当前登录用户的用户组
 */
// 管理员
define('USERGROUP_ADMINISTRATOR', 1);
// 禁用用户
define('USERGROUP_BAN', 2);
// 普通用户
define('USERGROUP_USER', 3);

/*
 * 时间
 */
// 一天的秒数
define('SECONDS_DAY', 86400);
// 一周的秒数
define('SECONDS_WEEK', 604800);
// 两周的秒数
define('SECONDS_TWO_WEEK', 1209600);
// 一月的秒数
define('SECONDS_MONTH', 2592000);
// 一年的秒数
define('SECONDS_YEAR', 31536000);
// 定义Redis常默认缓存时间: 一周
define('REDIS_DEFAULT_TIMEOUT', SECONDS_WEEK);

// 格式化时间相关
// 短类型 2011-8-12
define('FORMAT_DATE_SHORT', 'Y-m-d');
// 长类型 2011-8-12 11:34:00
define('FORMAT_DATE_LONG', 'Y-m-d H:i:s');
