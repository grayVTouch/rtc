/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : oa

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2020-02-14 18:58:35
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for oa_department
-- ----------------------------
DROP TABLE IF EXISTS `oa_department`;
CREATE TABLE `oa_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `depart_name` varchar(255) DEFAULT NULL COMMENT '部门名称',
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='部门表';

-- ----------------------------
-- Records of oa_department
-- ----------------------------
INSERT INTO `oa_department` VALUES ('1', '销售部', '2');
INSERT INTO `oa_department` VALUES ('2', '设计部', '1');
INSERT INTO `oa_department` VALUES ('3', '成本部', '3');
INSERT INTO `oa_department` VALUES ('4', '采购部', '6');
INSERT INTO `oa_department` VALUES ('5', '技术部', '5');
INSERT INTO `oa_department` VALUES ('6', '计划部', '7');
INSERT INTO `oa_department` VALUES ('7', '生产部', '10');

-- ----------------------------
-- Table structure for oa_design_assocciation
-- ----------------------------
DROP TABLE IF EXISTS `oa_design_assocciation`;
CREATE TABLE `oa_design_assocciation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL COMMENT '姓名',
  `order_id` int(11) DEFAULT NULL COMMENT '订单id',
  `comtent` varchar(255) DEFAULT NULL COMMENT '工作职责',
  `phone` varchar(255) DEFAULT NULL COMMENT '手机号码',
  `department_id` int(11) DEFAULT NULL COMMENT '部门id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='设计配合人员表';

-- ----------------------------
-- Records of oa_design_assocciation
-- ----------------------------

-- ----------------------------
-- Table structure for oa_model
-- ----------------------------
DROP TABLE IF EXISTS `oa_model`;
CREATE TABLE `oa_model` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '销售模式名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='销售模式表';

-- ----------------------------
-- Records of oa_model
-- ----------------------------
INSERT INTO `oa_model` VALUES ('1', 'B2B');
INSERT INTO `oa_model` VALUES ('2', 'B2C');
INSERT INTO `oa_model` VALUES ('3', '工程');

-- ----------------------------
-- Table structure for oa_order
-- ----------------------------
DROP TABLE IF EXISTS `oa_order`;
CREATE TABLE `oa_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` int(255) NOT NULL COMMENT '订单号',
  `client_name` varchar(255) DEFAULT NULL COMMENT '客户名称',
  `client_phone` varchar(255) DEFAULT NULL COMMENT '客户手机号码',
  `delivery_time` int(255) DEFAULT NULL COMMENT '送货时间',
  `project_address` varchar(255) DEFAULT NULL COMMENT '工程地址',
  `sales_model_id` int(11) DEFAULT NULL COMMENT '销售模式id',
  `note` varchar(255) DEFAULT NULL COMMENT '备注',
  `data` varchar(255) DEFAULT NULL COMMENT '资料',
  `quotation` varchar(255) DEFAULT NULL COMMENT '报价单',
  `is_cad` int(3) DEFAULT NULL COMMENT '是否需要cad',
  `designperson_id` int(11) DEFAULT NULL,
  `design_img` varchar(255) DEFAULT NULL COMMENT '设计图',
  `create_time` int(255) DEFAULT NULL,
  `money` varchar(255) DEFAULT NULL COMMENT '订单金额',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='客户表';

-- ----------------------------
-- Records of oa_order
-- ----------------------------
INSERT INTO `oa_order` VALUES ('1', '2147483647', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `oa_order` VALUES ('2', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '准备1', null, null, null, null, null, null, null);
INSERT INTO `oa_order` VALUES ('3', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '准备1', null, null, null, null, null, null, null);
INSERT INTO `oa_order` VALUES ('4', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '准备1', null, null, null, null, null, null, null);
INSERT INTO `oa_order` VALUES ('5', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '准备1', null, null, null, null, null, null, null);
INSERT INTO `oa_order` VALUES ('6', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '准备1', null, null, null, null, null, null, null);
INSERT INTO `oa_order` VALUES ('7', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '准备1', null, null, null, null, null, null, null);
INSERT INTO `oa_order` VALUES ('8', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '备注', null, null, null, null, null, null, null);
INSERT INTO `oa_order` VALUES ('9', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '备注', '', null, null, null, null, null, null);
INSERT INTO `oa_order` VALUES ('10', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '备注', '', null, null, null, null, null, null);
INSERT INTO `oa_order` VALUES ('11', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '备注', '', null, null, null, null, '1581671116', null);
INSERT INTO `oa_order` VALUES ('12', '2147483647', '客户A', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '备注', '', null, null, null, null, '1581671132', null);
INSERT INTO `oa_order` VALUES ('13', '2147483647', '客户b', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '备注', '', null, null, null, null, '1581671174', null);
INSERT INTO `oa_order` VALUES ('14', '2147483647', '客户b', '18755191026', '1581651909', '安徽省合肥市庐阳区寿春路百花大厦', '2', '备注', '', null, null, null, null, '1581671288', null);

-- ----------------------------
-- Table structure for oa_order_related
-- ----------------------------
DROP TABLE IF EXISTS `oa_order_related`;
CREATE TABLE `oa_order_related` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL COMMENT '订单id',
  `salesperson_id` int(11) DEFAULT NULL COMMENT '销售人员id',
  `sales_sub_time` int(255) DEFAULT NULL COMMENT '销售员提交时间',
  `sales_order_status` int(4) NOT NULL DEFAULT '0' COMMENT '销售 订单状态  0是待处理  1以处理  2是驳回',
  `designperson_id` int(11) DEFAULT NULL COMMENT '设计部人员id',
  `design_sub_time` int(255) DEFAULT NULL COMMENT '设计人员提交时间',
  `design_order_status` int(4) NOT NULL DEFAULT '0' COMMENT '设计 订单状态  0是待处理  1以处理  2是驳回',
  `jishu_person_id` int(11) DEFAULT NULL COMMENT '技术人员id',
  `jishu_sub_time` int(255) DEFAULT NULL COMMENT '技术提交时间',
  `jishu_order_status` int(4) NOT NULL DEFAULT '0' COMMENT '技术 订单状态  0是待处理  1以处理  2是驳回',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of oa_order_related
-- ----------------------------
INSERT INTO `oa_order_related` VALUES ('1', '8', null, '1581653968', '0', null, null, '0', null, null, '0');
INSERT INTO `oa_order_related` VALUES ('2', '9', '1', '1581668847', '0', '3', null, '0', null, null, '0');
INSERT INTO `oa_order_related` VALUES ('3', '10', '1', '1581668907', '0', '3', null, '0', null, null, '0');
INSERT INTO `oa_order_related` VALUES ('4', '11', '1', '1581671116', '0', '3', null, '0', null, null, '0');
INSERT INTO `oa_order_related` VALUES ('5', '12', '1', '1581671132', '0', '3', null, '0', null, null, '0');
INSERT INTO `oa_order_related` VALUES ('6', '13', '1', '1581671175', '0', '3', null, '0', null, null, '0');
INSERT INTO `oa_order_related` VALUES ('7', '14', '1', '1581671288', '0', '3', null, '0', null, null, '0');

-- ----------------------------
-- Table structure for oa_sales_assocciation
-- ----------------------------
DROP TABLE IF EXISTS `oa_sales_assocciation`;
CREATE TABLE `oa_sales_assocciation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL COMMENT '姓名',
  `order_id` int(11) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL COMMENT '手机号码',
  `department_id` int(11) DEFAULT NULL COMMENT '部门id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of oa_sales_assocciation
-- ----------------------------
INSERT INTO `oa_sales_assocciation` VALUES ('1', null, '5', null, null, '1');
INSERT INTO `oa_sales_assocciation` VALUES ('2', '销售小王', '6', null, '151427492418', '1');
INSERT INTO `oa_sales_assocciation` VALUES ('3', '销售小王', '7', '了解客户需要', '151427492418', '1');
INSERT INTO `oa_sales_assocciation` VALUES ('4', '销售小王', '8', '了解客户需要', '151427492418', '1');
INSERT INTO `oa_sales_assocciation` VALUES ('5', '销售小王', '9', '了解客户需要', '151427492418', '1');
INSERT INTO `oa_sales_assocciation` VALUES ('6', '销售小王', '10', '了解客户需要', '151427492418', '1');
INSERT INTO `oa_sales_assocciation` VALUES ('7', '销售小王', '11', '了解客户需要', '151427492418', '1');
INSERT INTO `oa_sales_assocciation` VALUES ('8', '销售小王', '12', '了解客户需要', '151427492418', '1');
INSERT INTO `oa_sales_assocciation` VALUES ('9', '销售小王', '13', '了解客户需要', '151427492418', '1');
INSERT INTO `oa_sales_assocciation` VALUES ('10', '销售小王', '14', '了解客户需要', '151427492418', '1');

-- ----------------------------
-- Table structure for oa_technology_assocciation
-- ----------------------------
DROP TABLE IF EXISTS `oa_technology_assocciation`;
CREATE TABLE `oa_technology_assocciation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL COMMENT '姓名',
  `order_id` int(11) DEFAULT NULL COMMENT '订单id',
  `comtent` varchar(255) DEFAULT NULL COMMENT '工作职责',
  `phone` varchar(255) DEFAULT NULL COMMENT '手机号码',
  `department_id` int(11) DEFAULT NULL COMMENT '部门id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='技术部配合人员表';

-- ----------------------------
-- Records of oa_technology_assocciation
-- ----------------------------

-- ----------------------------
-- Table structure for oa_user
-- ----------------------------
DROP TABLE IF EXISTS `oa_user`;
CREATE TABLE `oa_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL COMMENT '用户名',
  `password` varchar(255) DEFAULT NULL COMMENT '密码',
  `phone` varchar(255) DEFAULT NULL,
  `token` varchar(500) DEFAULT NULL COMMENT 'token',
  `position` varchar(255) DEFAULT NULL COMMENT '职位',
  `department_id` int(11) DEFAULT NULL COMMENT '部门id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='用户表';

-- ----------------------------
-- Records of oa_user
-- ----------------------------
INSERT INTO `oa_user` VALUES ('1', '张三', '123456', '18755192416', null, '销售部经理', '1');
INSERT INTO `oa_user` VALUES ('2', '李四', '123456', '15155613331', null, '销售部经理', '1');
INSERT INTO `oa_user` VALUES ('3', '王五', '123456', '15155613331', null, '设计部经理', '2');
