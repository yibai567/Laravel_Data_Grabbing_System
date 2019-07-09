/*
Navicat MySQL Data Transfer

Source Server         : 330657
Source Server Version : 50717
Source Host           : localhost:3306
Source Database       : jinse_crawl

Target Server Type    : MYSQL
Target Server Version : 50717
File Encoding         : 65001

Date: 2019-07-09 17:53:00
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for cms_apicustom
-- ----------------------------
DROP TABLE IF EXISTS `cms_apicustom`;
CREATE TABLE `cms_apicustom` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permalink` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tabel` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aksi` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kolom` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orderby` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_query_1` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql_where` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parameter` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `method_type` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parameters` longtext COLLATE utf8mb4_unicode_ci,
  `responses` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_apikey
-- ----------------------------
DROP TABLE IF EXISTS `cms_apikey`;
CREATE TABLE `cms_apikey` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `screetkey` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hit` int(11) DEFAULT NULL,
  `status` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_dashboard
-- ----------------------------
DROP TABLE IF EXISTS `cms_dashboard`;
CREATE TABLE `cms_dashboard` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_cms_privileges` int(11) DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_email_queues
-- ----------------------------
DROP TABLE IF EXISTS `cms_email_queues`;
CREATE TABLE `cms_email_queues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `send_at` datetime DEFAULT NULL,
  `email_recipient` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_from_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_from_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_cc_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_subject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_content` text COLLATE utf8mb4_unicode_ci,
  `email_attachments` text COLLATE utf8mb4_unicode_ci,
  `is_sent` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_email_templates
-- ----------------------------
DROP TABLE IF EXISTS `cms_email_templates`;
CREATE TABLE `cms_email_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cc_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_logs
-- ----------------------------
DROP TABLE IF EXISTS `cms_logs`;
CREATE TABLE `cms_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `useragent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `details` text COLLATE utf8mb4_unicode_ci,
  `id_cms_users` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1014 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_menus
-- ----------------------------
DROP TABLE IF EXISTS `cms_menus`;
CREATE TABLE `cms_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'url',
  `path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_dashboard` tinyint(1) NOT NULL DEFAULT '0',
  `id_cms_privileges` int(11) DEFAULT NULL,
  `sorting` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_menus_privileges
-- ----------------------------
DROP TABLE IF EXISTS `cms_menus_privileges`;
CREATE TABLE `cms_menus_privileges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cms_menus` int(11) DEFAULT NULL,
  `id_cms_privileges` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_moduls
-- ----------------------------
DROP TABLE IF EXISTS `cms_moduls`;
CREATE TABLE `cms_moduls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `controller` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_protected` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_notifications
-- ----------------------------
DROP TABLE IF EXISTS `cms_notifications`;
CREATE TABLE `cms_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cms_users` int(11) DEFAULT NULL,
  `content` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_privileges
-- ----------------------------
DROP TABLE IF EXISTS `cms_privileges`;
CREATE TABLE `cms_privileges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_superadmin` tinyint(1) DEFAULT NULL,
  `theme_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_privileges_roles
-- ----------------------------
DROP TABLE IF EXISTS `cms_privileges_roles`;
CREATE TABLE `cms_privileges_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_visible` tinyint(1) DEFAULT NULL,
  `is_create` tinyint(1) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  `is_edit` tinyint(1) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT NULL,
  `id_cms_privileges` int(11) DEFAULT NULL,
  `id_cms_moduls` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_settings
-- ----------------------------
DROP TABLE IF EXISTS `cms_settings`;
CREATE TABLE `cms_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `content_input_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataenum` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `helper` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `group_setting` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_statistics
-- ----------------------------
DROP TABLE IF EXISTS `cms_statistics`;
CREATE TABLE `cms_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_statistic_components
-- ----------------------------
DROP TABLE IF EXISTS `cms_statistic_components`;
CREATE TABLE `cms_statistic_components` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cms_statistics` int(11) DEFAULT NULL,
  `componentID` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `component_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_name` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sorting` int(11) DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `config` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_users
-- ----------------------------
DROP TABLE IF EXISTS `cms_users`;
CREATE TABLE `cms_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_cms_privileges` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for t_action
-- ----------------------------
DROP TABLE IF EXISTS `t_action`;
CREATE TABLE `t_action` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `discription` varchar(255) DEFAULT NULL,
  `vhost` varchar(100) DEFAULT NULL,
  `exchange` varchar(100) DEFAULT NULL,
  `exchange_type` tinyint(1) DEFAULT NULL,
  `routing_key` varchar(100) DEFAULT NULL,
  `queue` varchar(100) DEFAULT NULL,
  `customer_path` varchar(255) DEFAULT NULL,
  `params` text,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_alarm
-- ----------------------------
DROP TABLE IF EXISTS `t_alarm`;
CREATE TABLE `t_alarm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alarm_rule_id` int(10) DEFAULT NULL,
  `crawl_task_id` int(10) DEFAULT NULL COMMENT '任务id',
  `content` text COMMENT '报警内容',
  `operation_user` varchar(50) DEFAULT NULL COMMENT '处理用户',
  `operation_at` timestamp NULL DEFAULT NULL COMMENT '处理时间',
  `completion_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态：1、初始化；2、处理中；3、已处理；4、已忽略',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `c_r_index_taskid_ruleid_status` (`crawl_task_id`,`alarm_rule_id`,`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_alarm_result
-- ----------------------------
DROP TABLE IF EXISTS `t_alarm_result`;
CREATE TABLE `t_alarm_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned DEFAULT NULL COMMENT '类型 1 企业微信 2 短信',
  `content` varchar(200) DEFAULT NULL COMMENT '发送内容',
  `phone` varchar(200) DEFAULT NULL COMMENT '手机号,多个用逗号分开',
  `wework` varchar(200) DEFAULT NULL COMMENT '企业微信号,多个用逗号分开',
  `send_at` int(10) DEFAULT NULL COMMENT '发送时间戳',
  `status` tinyint(1) unsigned DEFAULT NULL COMMENT '状态 1发送中 2成功 3失败',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2208 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_alarm_rule
-- ----------------------------
DROP TABLE IF EXISTS `t_alarm_rule`;
CREATE TABLE `t_alarm_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '警报名称',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `expression` varchar(255) DEFAULT NULL COMMENT '表达式',
  `expression_value` varchar(255) DEFAULT NULL COMMENT '阈值',
  `receive_email` varchar(255) DEFAULT NULL COMMENT '接收邮箱',
  `receive_phone` varchar(255) DEFAULT NULL COMMENT '接收手机',
  `receive_wework` varchar(255) DEFAULT NULL COMMENT '接收企业微信',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态：1、初始化；2、启动；3、停止',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_app_auth
-- ----------------------------
DROP TABLE IF EXISTS `t_app_auth`;
CREATE TABLE `t_app_auth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `access_token` text,
  `expires_in` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for t_block_news
-- ----------------------------
DROP TABLE IF EXISTS `t_block_news`;
CREATE TABLE `t_block_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requirement_id` int(10) DEFAULT '0' COMMENT '需求id',
  `list_url` varchar(255) DEFAULT NULL COMMENT '列表url',
  `title` text COMMENT '标题',
  `description` text COMMENT '描述',
  `content` text COMMENT '内容',
  `detail_url` varchar(255) DEFAULT NULL COMMENT '地址',
  `show_time` varchar(100) DEFAULT NULL COMMENT '文章原始时间',
  `read_count` varchar(100) DEFAULT '0' COMMENT '数字',
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_r_id_index` (`requirement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_company
-- ----------------------------
DROP TABLE IF EXISTS `t_company`;
CREATE TABLE `t_company` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cn_name` varchar(100) DEFAULT NULL,
  `en_name` varchar(100) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  `url` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_crawl_node
-- ----------------------------
DROP TABLE IF EXISTS `t_crawl_node`;
CREATE TABLE `t_crawl_node` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '节点名称',
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '节点ip',
  `tag` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '命名标签',
  `region` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '所在区域',
  `docker_num` tinyint(3) unsigned DEFAULT '0' COMMENT 'docker数量',
  `max_task_num` tinyint(3) unsigned DEFAULT '0' COMMENT '最大任务数量',
  `log_path` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '日志保存路径',
  `status` tinyint(3) unsigned DEFAULT '1' COMMENT '节点状态, 1 可用 2 不可用',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for t_crawl_result
-- ----------------------------
DROP TABLE IF EXISTS `t_crawl_result`;
CREATE TABLE `t_crawl_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `crawl_task_id` int(10) unsigned DEFAULT '0' COMMENT '任务id',
  `original_data` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '原始数据md5',
  `task_start_time` datetime DEFAULT NULL COMMENT '任务开始时间',
  `task_end_time` datetime DEFAULT NULL COMMENT '任务结束时间',
  `setting_selectors` text COLLATE utf8mb4_unicode_ci COMMENT '选择器：抓取规则例：{"tilte" :".result c-container", "url" : "a.href"}',
  `setting_keywords` text COLLATE utf8mb4_unicode_ci COMMENT '匹配关键词',
  `setting_data_type` tinyint(3) unsigned DEFAULT '0' COMMENT '1、html 2、json 3、xml 暂不支持',
  `task_url` text COLLATE utf8mb4_unicode_ci COMMENT '任务url地址',
  `format_data` text COLLATE utf8mb4_unicode_ci COMMENT '格式化数据',
  `status` tinyint(3) unsigned DEFAULT '0' COMMENT '状态 1、未处理 2、已处理',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `c_r_index_task_id` (`crawl_task_id`),
  KEY `c_r_index_taskid_odata` (`crawl_task_id`,`original_data`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for t_crawl_result_v2
-- ----------------------------
DROP TABLE IF EXISTS `t_crawl_result_v2`;
CREATE TABLE `t_crawl_result_v2` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `crawl_task_id` int(10) unsigned NOT NULL,
  `fields` text COMMENT '格式化结果数据',
  `md5_fields` varchar(50) DEFAULT NULL COMMENT 'md5_fields字段',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '任务开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '任务结束时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态1、未处理 2、已处理',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_crawl_task
-- ----------------------------
DROP TABLE IF EXISTS `t_crawl_task`;
CREATE TABLE `t_crawl_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '任务名称',
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '任务描述',
  `resource_url` text COLLATE utf8mb4_unicode_ci COMMENT '资源URL',
  `cron_type` tinyint(3) unsigned DEFAULT '0' COMMENT 'cron类型',
  `selectors` text COLLATE utf8mb4_unicode_ci COMMENT '选择器',
  `api_fields` text COLLATE utf8mb4_unicode_ci COMMENT 'API类型选择器',
  `response_type` tinyint(3) unsigned DEFAULT '0' COMMENT '响应类型 1、API（默认只支持）2、邮件 3、短信 4、企业微信',
  `response_url` text COLLATE utf8mb4_unicode_ci COMMENT '发送接口地址',
  `keywords` text COLLATE utf8mb4_unicode_ci COMMENT '关键词',
  `response_params` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '参数',
  `test_result` text COLLATE utf8mb4_unicode_ci COMMENT '测试结果',
  `test_time` datetime DEFAULT NULL COMMENT '测试时间',
  `is_proxy` tinyint(3) DEFAULT '2' COMMENT '是否需要代理：1、是 2、否',
  `is_ajax` tinyint(3) DEFAULT '2' COMMENT '是否Ajax请求： 1、是 2、否',
  `is_wall` tinyint(3) DEFAULT '2' COMMENT '是否被墙：1、被墙 2、没有被墙',
  `is_login` tinyint(3) DEFAULT '2' COMMENT '是否需要登录：1、需要 2、不需要',
  `protocol` tinyint(3) DEFAULT '1' COMMENT '协议类型 1:http 2:https',
  `header` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'header头信息',
  `resource_type` tinyint(1) DEFAULT NULL COMMENT '类型：1 html（默认）2 json',
  `start_time` datetime DEFAULT NULL COMMENT '任务启动时间',
  `last_job_at` timestamp NULL DEFAULT NULL COMMENT '任务最后执行时间',
  `setting_id` int(10) unsigned DEFAULT '1' COMMENT '配置ID',
  `status` tinyint(3) unsigned DEFAULT '0' COMMENT '状态： 1、未启动 2、测试成功 3、测试失败 4、启动中 5、已停止 6、归档',
  `md5_params` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '给参数md5',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for t_crawl_task_setting
-- ----------------------------
DROP TABLE IF EXISTS `t_crawl_task_setting`;
CREATE TABLE `t_crawl_task_setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '任务名称',
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '任务描述',
  `url` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '资源URL',
  `selectors` text COLLATE utf8mb4_unicode_ci COMMENT '选择器：抓取规则例：{"tilte" :".result c-container", "url" : "a.href"}',
  `keywords` text COLLATE utf8mb4_unicode_ci COMMENT '匹配关键词',
  `data_type` tinyint(3) unsigned DEFAULT '0' COMMENT '1、html 2、json 3、xml 暂不支持',
  `content_type` tinyint(3) unsigned DEFAULT '0' COMMENT '内容类型1、list 2、content',
  `is_proxy` tinyint(3) unsigned DEFAULT '0' COMMENT '是否需要代理 1、需要代理 2、不需要代理',
  `response_type` tinyint(3) unsigned DEFAULT '0' COMMENT '响应类型 1、API（默认只支持）2、邮件 3、短信 4、企业微信',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '抓取模版内容',
  `template_file` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '脚本模版',
  `type` tinyint(3) unsigned DEFAULT '0' COMMENT '抓取模版类型 1 通用模版 2自定义模版',
  `status` tinyint(3) unsigned DEFAULT '0' COMMENT '状态 1、可用 2、不可用',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for t_data
-- ----------------------------
DROP TABLE IF EXISTS `t_data`;
CREATE TABLE `t_data` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `content_type` tinyint(1) DEFAULT '0' COMMENT '分类',
  `company` varchar(50) DEFAULT NULL COMMENT '公司名',
  `task_id` int(10) DEFAULT NULL,
  `task_run_log_id` int(10) unsigned DEFAULT NULL,
  `title` text COMMENT '标题',
  `md5_title` char(32) DEFAULT NULL,
  `md5_content` char(32) DEFAULT NULL,
  `content` longtext COMMENT '内容',
  `description` text,
  `detail_url` text COMMENT '地址',
  `show_time` varchar(100) DEFAULT NULL COMMENT '文章原始时间',
  `author` varchar(100) DEFAULT NULL COMMENT '作者',
  `read_count` varchar(100) DEFAULT '0' COMMENT '阅读数',
  `thumbnail` text COMMENT '缩略图',
  `screenshot` text COMMENT '截图',
  `language_type` tinyint(1) unsigned DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0' COMMENT '状态：1、正常|2、隐藏',
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `created_time` int(10) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_d_md5_title_index` (`md5_title`) USING BTREE,
  KEY `t_d_md5_content_index` (`md5_content`) USING BTREE,
  KEY `t_d_content_type_status_created_time_index` (`content_type`,`status`,`start_time`) USING BTREE,
  KEY `t_task_id_index` (`task_id`),
  KEY `t_task_trl_id_index` (`task_run_log_id`) USING BTREE,
  KEY `t_d_deleted_at` (`deleted_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_error_message
-- ----------------------------
DROP TABLE IF EXISTS `t_error_message`;
CREATE TABLE `t_error_message` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `raw` text COMMENT '原始数据',
  `msg` text COMMENT '错误信息',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_fast_news
-- ----------------------------
DROP TABLE IF EXISTS `t_fast_news`;
CREATE TABLE `t_fast_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requirement_id` int(10) DEFAULT '0' COMMENT '需求id',
  `list_url` varchar(255) DEFAULT NULL COMMENT '列表url',
  `title` text COMMENT '标题',
  `description` text COMMENT '描述',
  `content` text COMMENT '内容',
  `show_time` varchar(100) DEFAULT NULL COMMENT '文章原始时间',
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_r_id_index` (`requirement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_filter
-- ----------------------------
DROP TABLE IF EXISTS `t_filter`;
CREATE TABLE `t_filter` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `discription` varchar(255) DEFAULT NULL,
  `vhost` varchar(100) DEFAULT NULL,
  `exchange` varchar(100) DEFAULT NULL,
  `exchange_type` tinyint(1) DEFAULT NULL,
  `routing_key` varchar(100) DEFAULT NULL,
  `queue` varchar(100) DEFAULT NULL,
  `customer_path` varchar(255) DEFAULT NULL,
  `params` text,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_group_wx_message
-- ----------------------------
DROP TABLE IF EXISTS `t_group_wx_message`;
CREATE TABLE `t_group_wx_message` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `year` char(10) DEFAULT NULL COMMENT '年',
  `month` char(10) DEFAULT NULL,
  `day` char(10) DEFAULT NULL,
  `hour` char(10) DEFAULT NULL,
  `minutes` char(10) DEFAULT NULL,
  `times` char(10) DEFAULT NULL,
  `last_message` text,
  `status` tinyint(1) DEFAULT '0' COMMENT '0未标记，1已标记',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_history_topic
-- ----------------------------
DROP TABLE IF EXISTS `t_history_topic`;
CREATE TABLE `t_history_topic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(100) DEFAULT NULL COMMENT '网站分类',
  `company` varchar(50) DEFAULT NULL COMMENT '网站名称',
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '网站id',
  `title` text COMMENT '标题',
  `content` longtext COMMENT '内容',
  `tags` varchar(255) DEFAULT NULL COMMENT '标签',
  `show_time` varchar(100) DEFAULT NULL COMMENT '文章原始时间',
  `author` varchar(50) DEFAULT NULL COMMENT '作者',
  `read_count` varchar(100) DEFAULT NULL COMMENT '阅读数',
  `comment_count` varchar(100) DEFAULT NULL COMMENT '评论数',
  `detail_url` text COMMENT '详情url',
  `md5_url` char(32) DEFAULT NULL,
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '创建数据时间戳',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 1未抓取 2正在抓取 3抓取完成',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `t_ht_md5_url_unique` (`md5_url`)
) ENGINE=InnoDB AUTO_INCREMENT=88528 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_project_result
-- ----------------------------
DROP TABLE IF EXISTS `t_project_result`;
CREATE TABLE `t_project_result` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `content_type` tinyint(1) DEFAULT '0' COMMENT '分类',
  `company` varchar(50) DEFAULT NULL COMMENT '公司名',
  `task_id` int(10) DEFAULT NULL,
  `project_id` int(10) DEFAULT NULL,
  `task_run_log_id` int(10) unsigned DEFAULT NULL,
  `title` text COMMENT '标题',
  `description` text,
  `content` text COMMENT '内容',
  `detail_url` text COMMENT '地址',
  `show_time` varchar(100) DEFAULT NULL COMMENT '文章原始时间',
  `author` varchar(100) DEFAULT NULL COMMENT '作者',
  `read_count` varchar(100) DEFAULT '0' COMMENT '阅读数',
  `thumbnail` text COMMENT '缩略图',
  `screenshot` text COMMENT '截图',
  `language_type` tinyint(1) unsigned DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0' COMMENT '状态：1、正常|2、隐藏',
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `created_time` int(10) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_d_content_type_status_created_time_index` (`content_type`,`status`,`start_time`) USING BTREE,
  KEY `t_pr_deleted_at` (`deleted_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_queue_info
-- ----------------------------
DROP TABLE IF EXISTS `t_queue_info`;
CREATE TABLE `t_queue_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '队列名',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '描述信息',
  `current_length` int(10) unsigned DEFAULT '0' COMMENT '当前队列长度',
  `db` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '数据库',
  `is_proxy` tinyint(1) DEFAULT '0' COMMENT '是否需要翻墙：1-需要|2-不需要',
  `data_type` tinyint(1) DEFAULT '0' COMMENT '内容类型：1-html|2-json|3-截图',
  `is_capture_image` tinyint(1) DEFAULT '0' COMMENT '是否需要截图：1-true|2-false',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态：1-success|2-fail',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qi_data_type_proxy_status_db_index` (`db`,`is_proxy`,`data_type`,`is_capture_image`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for t_requirement_pool
-- ----------------------------
DROP TABLE IF EXISTS `t_requirement_pool`;
CREATE TABLE `t_requirement_pool` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `description` text,
  `img_description` varchar(255) DEFAULT NULL,
  `subscription_type` tinyint(1) unsigned DEFAULT '0',
  `company_id` int(10) DEFAULT NULL,
  `list_url` varchar(255) DEFAULT NULL,
  `category` tinyint(1) DEFAULT '0',
  `is_capture` tinyint(1) unsigned DEFAULT '0',
  `is_download_img` tinyint(1) unsigned DEFAULT '0',
  `status` tinyint(1) unsigned DEFAULT '0',
  `status_identity` tinyint(1) DEFAULT '0',
  `status_reason` varchar(255) DEFAULT NULL COMMENT '状态描述',
  `create_by` int(10) unsigned DEFAULT NULL,
  `operate_by` int(10) unsigned DEFAULT NULL,
  `language_type` tinyint(1) unsigned DEFAULT NULL,
  `requirement_type` tinyint(1) DEFAULT NULL,
  `cron_type` tinyint(1) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_script
-- ----------------------------
DROP TABLE IF EXISTS `t_script`;
CREATE TABLE `t_script` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `requirement_pool_id` int(10) DEFAULT NULL COMMENT '需求id',
  `name` varchar(100) DEFAULT NULL COMMENT '脚本名称',
  `description` text COMMENT '脚本描述',
  `list_url` text,
  `data_type` tinyint(1) DEFAULT NULL,
  `casper_config_id` int(10) DEFAULT NULL,
  `modules` text,
  `content` text,
  `last_generate_at` int(11) DEFAULT NULL,
  `company_id` int(10) DEFAULT NULL,
  `is_proxy` tinyint(1) DEFAULT NULL,
  `projects` varchar(100) DEFAULT NULL,
  `filters` text,
  `actions` text,
  `cron_type` tinyint(1) DEFAULT NULL,
  `language_type` tinyint(1) unsigned DEFAULT NULL,
  `ext` tinyint(1) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_script2
-- ----------------------------
DROP TABLE IF EXISTS `t_script2`;
CREATE TABLE `t_script2` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `list_url` text COMMENT '资源URL',
  `script_init_id` int(10) DEFAULT NULL,
  `languages_type` tinyint(1) DEFAULT '0' COMMENT '1、casperjs；2、html；3、api；',
  `step` text COMMENT '步骤',
  `last_generate_at` int(11) DEFAULT NULL COMMENT '最后生成时间',
  `operate_user` varchar(50) DEFAULT NULL COMMENT '操作用户',
  `next_script_id` int(10) unsigned DEFAULT NULL COMMENT '下一个脚本ID',
  `requirement_pool_id` int(10) DEFAULT NULL COMMENT '需求ID',
  `is_report` tinyint(1) DEFAULT NULL COMMENT '是否上报',
  `is_proxy` tinyint(4) DEFAULT '0',
  `is_download` tinyint(1) DEFAULT NULL COMMENT '是否下载图片',
  `generate_type` tinyint(1) DEFAULT '0' COMMENT 'script生成模式: 1模块模式 2内容模式',
  `script_type` tinyint(1) DEFAULT '0' COMMENT '脚本类型  1 JS 2 PHP',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 1.初始化、2.已生成',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_script_config
-- ----------------------------
DROP TABLE IF EXISTS `t_script_config`;
CREATE TABLE `t_script_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `load_images` tinyint(1) DEFAULT NULL COMMENT 'casper pageSettings.loadImages',
  `load_plugins` tinyint(1) DEFAULT NULL,
  `log_level` varchar(10) DEFAULT NULL COMMENT 'casper  logLevel(debug|error)',
  `verbose` tinyint(1) DEFAULT NULL COMMENT 'casper verbose',
  `width` varchar(10) DEFAULT NULL COMMENT 'casper  viewportSize.width',
  `height` varchar(10) DEFAULT NULL COMMENT 'casper  viewportSize.height',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_script_model
-- ----------------------------
DROP TABLE IF EXISTS `t_script_model`;
CREATE TABLE `t_script_model` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(50) DEFAULT NULL COMMENT '模块名称',
  `description` text COMMENT '描述',
  `structure` text COMMENT '代码结构casper.capture($1, $2);',
  `parameters` text COMMENT '参数规则',
  `system_type` tinyint(1) DEFAULT NULL COMMENT '类型1、预生成模块，2、用户自定义模块 3.基本模板',
  `sort` int(10) unsigned DEFAULT NULL COMMENT '排序',
  `data_type` tinyint(1) DEFAULT '0' COMMENT '1、casperjs；2、html；3、api；',
  `operate_user` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_task
-- ----------------------------
DROP TABLE IF EXISTS `t_task`;
CREATE TABLE `t_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `script_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `list_url` text,
  `data_type` tinyint(1) unsigned DEFAULT '0',
  `script_path` varchar(255) DEFAULT NULL,
  `requirement_pool_id` int(10) DEFAULT NULL,
  `company_id` int(10) DEFAULT NULL,
  `is_proxy` tinyint(1) DEFAULT NULL,
  `projects` varchar(100) DEFAULT NULL,
  `filters` text,
  `actions` text,
  `cron_type` tinyint(1) unsigned DEFAULT '0',
  `language_type` tinyint(1) unsigned DEFAULT NULL,
  `ext` varchar(100) DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT '0',
  `test_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1成功，2失败',
  `test_url` text,
  `test_result` longtext,
  `last_test_start_at` timestamp NULL DEFAULT NULL,
  `last_test_end_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_t_script_id_index` (`script_id`) USING BTREE,
  KEY `t_t_ct_lt_s_index` (`cron_type`,`data_type`,`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_task_action_map
-- ----------------------------
DROP TABLE IF EXISTS `t_task_action_map`;
CREATE TABLE `t_task_action_map` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `task_id` int(10) DEFAULT NULL,
  `project_id` int(10) DEFAULT NULL,
  `action_id` int(10) DEFAULT NULL,
  `params` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `t_tam_tid_pid_aid_deleted_at_unique` (`task_id`,`project_id`,`action_id`,`deleted_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_task_filter_map
-- ----------------------------
DROP TABLE IF EXISTS `t_task_filter_map`;
CREATE TABLE `t_task_filter_map` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `task_id` int(10) DEFAULT NULL,
  `project_id` int(10) DEFAULT NULL,
  `filter_id` int(10) DEFAULT NULL,
  `params` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `t_tfm_tid_pid_fid_unique` (`task_id`,`project_id`,`filter_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_task_last_run_log
-- ----------------------------
DROP TABLE IF EXISTS `t_task_last_run_log`;
CREATE TABLE `t_task_last_run_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned DEFAULT NULL,
  `last_job_at` timestamp NULL DEFAULT NULL,
  `run_times` int(10) unsigned DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_t_s_task_id` (`task_id`,`last_job_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_task_project_map
-- ----------------------------
DROP TABLE IF EXISTS `t_task_project_map`;
CREATE TABLE `t_task_project_map` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `task_id` int(10) DEFAULT NULL,
  `project_id` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_tpm_tid_index` (`task_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_task_run_log
-- ----------------------------
DROP TABLE IF EXISTS `t_task_run_log`;
CREATE TABLE `t_task_run_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned DEFAULT NULL,
  `start_job_at` timestamp NULL DEFAULT NULL,
  `end_job_at` timestamp NULL DEFAULT NULL,
  `result_count` int(10) unsigned DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_t_r_l_task_id` (`task_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_task_statistics
-- ----------------------------
DROP TABLE IF EXISTS `t_task_statistics`;
CREATE TABLE `t_task_statistics` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `task_id` int(10) DEFAULT NULL,
  `data_type` tinyint(1) DEFAULT NULL,
  `is_proxy` tinyint(1) DEFAULT NULL,
  `cron_type` tinyint(1) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `total_result` int(10) DEFAULT '0',
  `last_job_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_d_t_index` (`data_type`) USING BTREE,
  KEY `t_i_p_index` (`is_proxy`) USING BTREE,
  KEY `t_c_t_index` (`cron_type`) USING BTREE,
  KEY `t_status_index` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_wechat_coin_news
-- ----------------------------
DROP TABLE IF EXISTS `t_wechat_coin_news`;
CREATE TABLE `t_wechat_coin_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coin_name` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `detail_url` text,
  `description` text COMMENT '描述',
  `wechat_subscription_number` varchar(100) DEFAULT NULL,
  `publish_time` int(10) unsigned DEFAULT NULL,
  `md5_all` char(32) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_wcn_md5_all` (`md5_all`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3250 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_wechat_server
-- ----------------------------
DROP TABLE IF EXISTS `t_wechat_server`;
CREATE TABLE `t_wechat_server` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wechat_name` varchar(100) DEFAULT NULL COMMENT '微信名称',
  `room_name` varchar(100) DEFAULT NULL COMMENT '群名称',
  `listen_type` tinyint(1) unsigned DEFAULT NULL COMMENT '1、群监听，2，公众号监听',
  `email` varchar(50) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT NULL COMMENT '1、初始化，2、已启动，2、已停止',
  `start_at` datetime DEFAULT NULL COMMENT '启动时间',
  `stop_at` datetime DEFAULT NULL COMMENT '停止时间',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_wechat_server_log
-- ----------------------------
DROP TABLE IF EXISTS `t_wechat_server_log`;
CREATE TABLE `t_wechat_server_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wechat_server_id` int(10) unsigned DEFAULT NULL,
  `content` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=335 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_wx_message
-- ----------------------------
DROP TABLE IF EXISTS `t_wx_message`;
CREATE TABLE `t_wx_message` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `group_wx_message_id` int(10) DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL COMMENT '微信用户名称',
  `room_name` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `content` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_wx_group_id_index` (`group_wx_message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=419 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_wx_message2
-- ----------------------------
DROP TABLE IF EXISTS `t_wx_message2`;
CREATE TABLE `t_wx_message2` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `urls` text,
  `start_time` timestamp NULL DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `content` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for t_wx_official_message
-- ----------------------------
DROP TABLE IF EXISTS `t_wx_official_message`;
CREATE TABLE `t_wx_official_message` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `urls` text,
  `start_time` timestamp NULL DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `content` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- View structure for view_name
-- ----------------------------
DROP VIEW IF EXISTS `view_name`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `view_name` AS select count(0) AS `初始化` from `t_crawl_task` where (`t_crawl_task`.`status` = 1) ;
