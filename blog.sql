/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50525
Source Host           : localhost:3306
Source Database       : blog1

Target Server Type    : MYSQL
Target Server Version : 50525
File Encoding         : 65001

Date: 2013-11-20 15:54:42
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `categories`
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `lang_id` int(11) unsigned NOT NULL,
  `status` int(1) DEFAULT '1',
  `url` varchar(255) NOT NULL,
  `renderer` mediumtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`) USING BTREE,
  KEY `languages` (`lang_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of categories
-- ----------------------------
INSERT INTO `categories` VALUES ('1', 'Posts', null, '0', '1', 'posts', 'posts');

-- ----------------------------
-- Table structure for `languages`
-- ----------------------------
DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lang` varchar(3) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of languages
-- ----------------------------

-- ----------------------------
-- Table structure for `menu_group`
-- ----------------------------
DROP TABLE IF EXISTS `menu_group`;
CREATE TABLE `menu_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `lang_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `languages` (`lang_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of menu_group
-- ----------------------------
INSERT INTO `menu_group` VALUES ('1', 'Main Menu', 'main_menu', '2');

-- ----------------------------
-- Table structure for `menu_item`
-- ----------------------------
DROP TABLE IF EXISTS `menu_item`;
CREATE TABLE `menu_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  `order` int(2) unsigned NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `caption` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_group` (`group_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of menu_item
-- ----------------------------
INSERT INTO `menu_item` VALUES ('1', '0', '1', '1', '0', 'Home', '/');
INSERT INTO `menu_item` VALUES ('2', '0', '1', '2', '0', 'About Us', 'about-us');
INSERT INTO `menu_item` VALUES ('3', '0', '1', '4', '0', 'Some Page', 'some-post-2');
INSERT INTO `menu_item` VALUES ('4', '0', '1', '3', '0', 'Posts', 'posts');

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of models
-- ----------------------------
INSERT INTO `models` VALUES ('1', '1', 'Model');
INSERT INTO `models` VALUES ('3', '2', 'MenuInstaller');
INSERT INTO `models` VALUES ('4', '4', 'PageInstaller');
INSERT INTO `models` VALUES ('8', '1', 'AdminInstaller');
INSERT INTO `models` VALUES ('9', '2', 'TranslatorInstaller');
INSERT INTO `models` VALUES ('10', '1', 'MenuModel');
INSERT INTO `models` VALUES ('11', '1', 'TranslatorModel');
INSERT INTO `models` VALUES ('12', '1', 'PageModel');
INSERT INTO `models` VALUES ('13', '1', 'CategoryModel');
INSERT INTO `models` VALUES ('14', '1', 'UserModel');

-- ----------------------------
-- Table structure for `pages`
-- ----------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `intro` mediumtext,
  `content` text,
  `lang_id` int(11) unsigned NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `type` enum('post','page','static') DEFAULT 'post',
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `status` int(1) unsigned DEFAULT '1',
  `views` int(10) NOT NULL DEFAULT '1',
  `downloads` int(10) NOT NULL DEFAULT '0',
  `comments` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`) USING BTREE,
  KEY `lang_id` (`lang_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pages
-- ----------------------------
INSERT INTO `pages` VALUES ('1', 'about-us', 'Welcomte to CMS \"Mitoncha\"', 'Shavkat Khamidjanov', '<span style=\"font-weight: bold;\">Some intro herefdfsdfsdf1</span>', 'Features:<div class=\"post_data\">\r\n    <ol><li>MVC/OOP Design</li><li>Modular Hierarchy &amp; Custom Extendability</li><li>MultiTemplate &amp; MultiDesign Render Fallback</li><li>Pagination and Form Builder</li><li>Custom Bootstrap Friendly</li><li>FullPage Cache Layer</li><li>Admin Backend Separate &amp; Cookie Based Login</li><li>Flexible Event Dispatching</li><li>Database PDO Persistence and Object based Model Management</li><li>Custom Module Installation &amp; Central Configuration Management</li><li>Dynamic Translation of Interface</li><li>Request, Cookie and Session Management</li><li>Developer Tools &amp; Debug LoggeDevelopment continues ...</li></ol><p></p><table class=\"table table-bordered\"><tbody><tr><td>fdfsdf<br></td><td>sdfsdf<br></td><td>sdfsdf<br></td><td>sdfdsf<br></td><td>sdfsf<br></td></tr><tr><td><br></td><td><br></td><td><br></td><td><br></td><td><br></td></tr><tr><td><br></td><td><br></td><td><br></td><td><br></td><td><br></td></tr><tr><td><br></td><td><br></td><td><br></td><td><br></td><td><br></td></tr><tr><td><br></td><td><br></td><td><br></td><td><br></td><td><br></td></tr></tbody></table><br><p></p>    </div>\r\n', '0', '1384708763.jpg', 'CMS, CMS Mitoncha, Powerfull CMS, MVC CMS, Test1', 'Mitoncha CMS offers flexible, scalable blog solutions designed to help community succeed online. It grows and continues to development. Some Description1', 'page', '2013-11-17 22:39:57', '2013-11-06 07:54:22', '1', '1', '0', '2');
INSERT INTO `pages` VALUES ('2', 'some-post-5', 'Some post ', null, null, '<p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\r\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\r\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-10-27 06:19:24', '1', '12', '33', '10');
INSERT INTO `pages` VALUES ('3', 'nex-post', 'Next Beautiful Post ', null, null, ' <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', '1384943012.png', null, null, 'post', '2013-11-20 15:23:32', '2013-11-18 07:42:59', '1', '13', '36', '11');
INSERT INTO `pages` VALUES ('4', 'some-post', 'Some post ', null, null, '<p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\r\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\r\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-12-06 06:19:24', '1', '15', '42', '13');
INSERT INTO `pages` VALUES ('5', 'nex-post-1', 'Next Beautiful Post ', null, null, ' <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-11-23 07:42:59', '1', '11', '30', '10');
INSERT INTO `pages` VALUES ('6', 'some-post-22', 'Some post', null, null, '<p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\r\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\r\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-12-18 06:19:24', '1', '133', '396', '101');
INSERT INTO `pages` VALUES ('7', 'nex-post-11', 'Next Beautiful Post ', null, null, ' <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-11-04 07:42:59', '1', '14', '39', '12');
INSERT INTO `pages` VALUES ('8', 'some-post-a', 'Some post ', null, null, '<p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\r\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\r\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-11-12 06:19:24', '1', '12', '33', '10');
INSERT INTO `pages` VALUES ('9', 'nex-post-a', 'Next Beautiful Post ', null, null, ' <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-11-17 07:42:59', '1', '1', '0', '2');
INSERT INTO `pages` VALUES ('10', 'some-post-f', 'Some post ', null, null, '<p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\r\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\r\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-11-18 06:19:24', '1', '112', '333', '85');
INSERT INTO `pages` VALUES ('18', 'some-post-fq', 'Some post ', null, null, '<p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\r\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\r\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-09-29 06:19:24', '0', '14', '39', '12');
INSERT INTO `pages` VALUES ('19', 'some-post-fds', 'Some post', null, null, '<p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\r\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\r\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-11-18 06:19:24', '1', '12', '33', '10');
INSERT INTO `pages` VALUES ('11', 'some-post-fqs', 'Some post', null, null, '<p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\r\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\r\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-11-08 06:19:24', '1', '18', '51', '15');
INSERT INTO `pages` VALUES ('12', 'nex-post-aq', 'Next Beautiful Post', null, null, ' <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-09-10 07:42:59', '1', '19', '54', '16');
INSERT INTO `pages` VALUES ('13', 'nex-post-bq', 'Next Beautiful Post', null, null, ' <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-08-20 07:42:59', '1', '128', '381', '97');
INSERT INTO `pages` VALUES ('14', 'some-post-qs', 'Some post', null, null, '<p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\r\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\r\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-11-10 06:19:24', '1', '154', '459', '117');
INSERT INTO `pages` VALUES ('15', 'some-post-qsd', 'Some post', null, null, '<p>Magento offers flexible, scalable eCommerce solutions designed to help businesses grow and succeed online. The Magento platform is trusted by more than 200,000 businesses, including some of the world\'s leading brands.</p>\r\n        <p>Customers choose Magento because our cost-effective solutions—built on open source technology—enable businesses of all sizes to control and customize the look and feel, content, and functionality of their online stores.</p>\r\n        <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-07-30 06:19:24', '1', '124', '369', '94');
INSERT INTO `pages` VALUES ('16', 'nex-post-sqs', 'Next Beautiful Post', null, null, ' <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-10-27 07:42:59', '1', '16', '45', '13');
INSERT INTO `pages` VALUES ('17', 'nex-post-sq', 'Next Beautiful Post', null, null, ' <p>We offer a range of resources, support, and consulting services to help our customers get the most from their Magento deployments, including education, training, and developer certification programs. Our global community of partners and developers gives customers access to robust third-party extensions and certified professional integration help.</p>\r\n        <p>Magento is owned by eBay Inc., a global leader in commerce technology. Our relationship enables us to offer our customers, partners, and community members a wealth of experience and resources in commerce-related technologies, as well as access to world-class, branded capabilities from eBay Marketplaces, PayPal, eBay Enterprise, and others.\r\n        If you’re new to working with Magento, welcome. We look forward to helping you grow your business.</p>\r\n    ', '0', 'thumb.jpg', null, null, 'post', null, '2013-12-07 07:42:59', '1', '17', '48', '14');
INSERT INTO `pages` VALUES ('20', 'sidebar', 'Static Block Test', null, null, '<div class=\"well\">\n        <h4>Side Widget Well</h4>\n\n        <p>Bootstrap\'s default well\'s work great for side widgets! What is a widget anyways...?</p>\n    </div>', '0', null, null, null, 'static', null, '2013-11-09 01:16:51', '1', '1', '0', '0');
INSERT INTO `pages` VALUES ('21', 'footer', 'Footer Notes', null, null, ' <div class=\"row\">\r\n        <div class=\"col col-lg-2 col-sm-6\">\r\n            <ul>\r\n                <li><a href=\"/about-us\">About</a></li>\r\n                <li><a href=\"/contacts\">Contacts</a></li>\r\n                <li><a href=\"/feedback\">Feedback</a></li>\r\n            </ul>\r\n        </div>\r\n        <div class=\"col col-lg-2 col-sm-6\">\r\n            <ul>\r\n                <li><a href=\"/about-us\">About</a></li>\r\n                <li><a href=\"/contacts\">Contacts</a></li>\r\n                <li><a href=\"/feedback\">Feedback</a></li>\r\n            </ul>\r\n        </div>\r\n        <div class=\"col col-lg-5 \" >\r\n                    Mitoncha CMS offers flexible, scalable blog solutions designed to help community succeed online.\r\n                    It grows and continues to development. Help us to keep it healthy. <a href=\"#\">BugReporting</a>.\r\n                </div>\r\n        <div class=\"col col-lg-3 \">\r\n            <p>Copyright &copy; Shavkat Khamidjanov 2013 &middot; Powered by <i class=\"fa\"></i>Mitoncha CMS<i class=\"fa\"></i></p>\r\n        </div>\r\n\r\n    </div>', '0', null, null, null, 'static', null, '2013-11-09 02:19:52', '1', '1', '0', '0');

-- ----------------------------
-- Table structure for `page_categories`
-- ----------------------------
DROP TABLE IF EXISTS `page_categories`;
CREATE TABLE `page_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`) USING BTREE,
  KEY `category_id` (`category_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of page_categories
-- ----------------------------
INSERT INTO `page_categories` VALUES ('1', '1', '1');
INSERT INTO `page_categories` VALUES ('2', '2', '1');
INSERT INTO `page_categories` VALUES ('3', '3', '1');
INSERT INTO `page_categories` VALUES ('4', '4', '1');
INSERT INTO `page_categories` VALUES ('5', '5', '1');
INSERT INTO `page_categories` VALUES ('6', '6', '1');
INSERT INTO `page_categories` VALUES ('7', '7', '1');
INSERT INTO `page_categories` VALUES ('8', '8', '1');
INSERT INTO `page_categories` VALUES ('9', '9', '1');
INSERT INTO `page_categories` VALUES ('10', '10', '1');
INSERT INTO `page_categories` VALUES ('11', '11', '1');
INSERT INTO `page_categories` VALUES ('12', '12', '1');
INSERT INTO `page_categories` VALUES ('13', '13', '1');
INSERT INTO `page_categories` VALUES ('14', '14', '1');
INSERT INTO `page_categories` VALUES ('15', '15', '1');
INSERT INTO `page_categories` VALUES ('16', '16', '1');
INSERT INTO `page_categories` VALUES ('17', '17', '1');
INSERT INTO `page_categories` VALUES ('18', '18', '1');
INSERT INTO `page_categories` VALUES ('19', '19', '1');

-- ----------------------------
-- Table structure for `translations`
-- ----------------------------
DROP TABLE IF EXISTS `translations`;
CREATE TABLE `translations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `lang_id` int(11) unsigned NOT NULL,
  `translation` mediumtext,
  PRIMARY KEY (`id`),
  KEY `lang_id` (`lang_id`) USING BTREE,
  KEY `word` (`word`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of translations
-- ----------------------------

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` int(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('1', 'Shavkat', 'homidjonov@gmail.com', '9b195a60af993b9afbe150c04ad200e5478d973b348c8fb4232326e694bc150d49c3a21572', 'admin', '1');
