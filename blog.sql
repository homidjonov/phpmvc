/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50525
Source Host           : localhost:3306
Source Database       : blog

Target Server Type    : MYSQL
Target Server Version : 50525
File Encoding         : 65001

Date: 2013-10-24 01:55:18
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `models`
-- ----------------------------
DROP TABLE IF EXISTS `models`;
CREATE TABLE `models` (
  `model_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `version` int(3) NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`model_id`),
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of models
-- ----------------------------
INSERT INTO `models` VALUES ('3', '1', 'Model');
INSERT INTO `models` VALUES ('8', '1', 'PageModel');

-- ----------------------------
-- Table structure for `pages`
-- ----------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `content` text,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `status` int(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pages
-- ----------------------------
INSERT INTO `pages` VALUES ('1', 'about-us', 'About Us', 'About Us  - eCommerce Software for Growth', '   <p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\n    ', 'open source ecommerce, open-source ecommerce, shopping cart, e-commerce, online business, commerce, software, platform, framework, ecommerce software, ecommerce platform', 'Magento is the eCommerce software platform for growth that promises to revolutionize the industry. Its modular architecture and unprecedented flexibility means your business is no longer constrained by your eCommerce platform. Magento is total control', '2013-10-23 23:31:47', '1');
