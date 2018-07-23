SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `ssi` DEFAULT CHARACTER SET latin1 ;
USE `ssi` ;

-- -----------------------------------------------------
-- Table `design_template_categories`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `design_template_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(128) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id` (`id` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `design_templates`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `design_templates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(128) NOT NULL ,
  `category_id` INT(11) NOT NULL ,
  `preview_image_id` INT(11) NOT NULL DEFAULT '-1' ,
  `json` LONGTEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id` (`id` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 7
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `designs`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `designs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `json` LONGTEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `image_categories`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `image_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(128) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id` (`id` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `images`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `category_id` INT(11) NOT NULL ,
  `name` VARCHAR(128) NOT NULL ,
  `data` BLOB NOT NULL ,
  `user_id` INT(11) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id` (`id` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 38
DEFAULT CHARACTER SET = latin1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;



-- -----------------------------------------------------
-- Table `design_template_categories`
-- -----------------------------------------------------
INSERT INTO `design_template_categories` (`id`,`name`) VALUES (1,'Test 1');
INSERT INTO `design_template_categories` (`id`,`name`) VALUES (2,'Test 2');


-- -----------------------------------------------------
-- Table `design_templates`
-- -----------------------------------------------------
INSERT INTO `design_templates` (`id`,`name`,`category_id`,`preview_image_id`,`json`) VALUES (1,'Party Guy',2,3,'{"selected":"2","elements":[{"className":"BorderElement","editAllowMove":false,"type":0,"size":30,"edgeRadius":30,"position":{"x1":-347.5269590222861,"y1":-196.15384615384613,"x2":345.3271028037383,"y2":195.79439252336448},"border":{"id":"stars"}},{"className":"ImageElement","editAllowMove":false,"position":{"x1":-0.8878504672899226,"y1":-157.71028037383178,"x2":307.4299065420563,"y2":153.03738317757012},"updateSizeOnLoad":false,"imageSrc":"design_part/get_image.php?id=37&color=black"},{"className":"TextElement","editAllowMove":false,"type":0,"text":"Line 1                                           ","bold":false,"italic":false,"font":"Verdana","inverted":false,"size":40,"angle":1.5707963267948966,"position":{"x1":-301.4018691588785,"y1":-152.80373831775705,"x2":290.18691588785043,"y2":-81.77570093457939,"angle":1.5707963267948966}},{"className":"TextElement","editAllowMove":false,"type":0,"text":"Line 2                                           ","bold":false,"italic":false,"font":"Verdana","inverted":false,"size":40,"angle":1.5707963267948966,"position":{"x1":-312.61682242990656,"y1":-92.0560747663552,"x2":301.40186915887847,"y2":-23.83177570093447,"angle":1.5707963267948966}},{"className":"TextElement","editAllowMove":false,"type":0,"text":"Single line text","bold":false,"italic":false,"font":"Verdana","inverted":false,"size":40,"angle":1.5707963267948966,"position":{"x1":-305.1401869158879,"y1":-100.4672897196262,"x2":65.88785046728977,"y2":84.57943925233644,"angle":1.5707963267948966}},{"className":"TextElement","editAllowMove":false,"type":0,"text":"Needs to support multiple","bold":false,"italic":false,"font":"Verdana","inverted":false,"size":40,"angle":1.5707963267948966,"position":{"x1":-305.14018691588785,"y1":-98.5981308411215,"x2":122.89719626168225,"y2":201.4018691588785,"angle":1.5707963267948966}},{"className":"TextElement","editAllowMove":false,"type":0,"text":"And allignment","bold":false,"italic":false,"font":"Verdana","inverted":false,"size":40,"angle":1.5707963267948966,"position":{"x1":-316.3551401869159,"y1":-43.45794392523365,"x2":34.112149532710276,"y2":256.5420560747664,"angle":1.5707963267948966}}],"pageParams":{"type":1,"width":700,"height":400}}');
INSERT INTO `design_templates` (`id`,`name`,`category_id`,`preview_image_id`,`json`) VALUES (4,'Box 700x400',1,2,'{"selected":"-1","elements":[],"pageParams":{"type":1,"width":700,"height":400}}');
INSERT INTO `design_templates` (`id`,`name`,`category_id`,`preview_image_id`,`json`) VALUES (5,'Circle 600x600',1,2,'{"selected":"-1","elements":[],"pageParams":{"type":2,"width":600,"height":600}}');
INSERT INTO `design_templates` (`id`,`name`,`category_id`,`preview_image_id`,`json`) VALUES (6,'Complex Circle',2,3,'{"selected":-1,"elements":[{"className":"BorderElement","editAllowMove":false,"type":1,"size":30,"edgeRadius":30,"position":{"x1":-295.1627906976744,"y1":-295.5049378783052,"x2":296.76744186046517,"y2":293.6588085377508},"border":{"id":"rope"}},{"className":"BorderElement","editAllowMove":false,"type":1,"size":15,"edgeRadius":30,"position":{"x1":-150,"y1":-150,"x2":150,"y2":150},"border":{"id":"stripes"}},{"className":"TextElement","editAllowMove":false,"type":1,"text":"............................................. Text Line 1 .............................................","bold":false,"italic":false,"font":"Verdana","inverted":false,"size":40,"angle":1.5707963267948966,"position":{"x1":-266.43835616438355,"y1":-263.6986301369863,"x2":266.43835616438355,"y2":263.6986301369863,"angle":1.5707963267948966}},{"className":"TextElement","editAllowMove":false,"type":1,"text":"............................................. Text Line 2 .............................................","bold":false,"italic":false,"font":"Verdana","inverted":true,"size":40,"angle":-1.5862357127677,"position":{"x1":-225.34246575342468,"y1":-221.2328767123288,"x2":226.71232876712327,"y2":221.2328767123288,"angle":-1.5862357127677}},{"className":"ImageElement","editAllowMove":false,"position":{"x1":-100,"y1":-38,"x2":100,"y2":38},"updateSizeOnLoad":false,"imageSrc":"design_part/get_image.php?id=3&color=black"}],"pageParams":{"type":2,"width":600,"height":600}}');

-- -----------------------------------------------------
-- Table `designs`
-- -----------------------------------------------------
INSERT INTO `designs` (`id`,`json`) VALUES (1,'');
INSERT INTO `designs` (`id`,`json`) VALUES (2,'{"selected":"3","elements":[{"className":"BorderElement","editAllowMove":false,"type":1,"size":30,"edgeRadius":30,"position":{"x1":-295.1627906976744,"y1":-295.5049378783052,"x2":296.76744186046517,"y2":293.6588085377508},"border":{"id":"rope"}},{"className":"BorderElement","editAllowMove":false,"type":1,"size":15,"edgeRadius":30,"position":{"x1":-150,"y1":-150,"x2":150,"y2":150},"border":{"id":"stripes"}},{"className":"TextElement","editAllowMove":false,"type":1,"text":"............................................. Text Line 1 .............................................","bold":false,"italic":false,"font":"Verdana","inverted":false,"size":40,"angle":1.5707963267948966,"position":{"x1":-266.43835616438355,"y1":-263.6986301369863,"x2":266.43835616438355,"y2":263.6986301369863,"angle":1.5707963267948966}},{"className":"TextElement","editAllowMove":false,"type":1,"text":"............................................. Text Line 2 .............................................","bold":false,"italic":false,"font":"Verdana","inverted":true,"size":40,"angle":-1.5862357127677,"position":{"x1":-225.34246575342468,"y1":-221.2328767123288,"x2":226.71232876712327,"y2":221.2328767123288,"angle":-1.5862357127677}},{"className":"ImageElement","editAllowMove":false,"position":{"x1":-100,"y1":-38,"x2":100,"y2":38},"updateSizeOnLoad":false,"imageSrc":"design_part/get_image.php?id=3&color=black"}],"pageParams":{"type":2,"width":600,"height":600}}');
INSERT INTO `designs` (`id`,`json`) VALUES (3,'');
INSERT INTO `designs` (`id`,`json`) VALUES (4,'');


-- -----------------------------------------------------
-- Table `image_categories`
-- -----------------------------------------------------
INSERT INTO `image_categories` (`id`,`name`) VALUES (1,'User Uploaded');
INSERT INTO `image_categories` (`id`,`name`) VALUES (2,'Office');
INSERT INTO `image_categories` (`id`,`name`) VALUES (3,'Sports');


-- -----------------------------------------------------
-- Table `images`
-- -----------------------------------------------------
INSERT INTO `images` (`id`,`category_id`,`name`,`data`,`user_id`) VALUES (1,1,'Accepted',?,1);
INSERT INTO `images` (`id`,`category_id`,`name`,`data`,`user_id`) VALUES (2,2,'Rush',?,-1);
INSERT INTO `images` (`id`,`category_id`,`name`,`data`,`user_id`) VALUES (3,2,'Copy',?,-1);
INSERT INTO `images` (`id`,`category_id`,`name`,`data`,`user_id`) VALUES (6,2,'air mail',?,-1);
INSERT INTO `images` (`id`,`category_id`,`name`,`data`,`user_id`) VALUES (35,1,'555583_10151971435735640_1959747875_n.jpg',?,1000);
INSERT INTO `images` (`id`,`category_id`,`name`,`data`,`user_id`) VALUES (36,1,'555583_10151971435735640_1959747875_n.jpg',?,1000);
INSERT INTO `images` (`id`,`category_id`,`name`,`data`,`user_id`) VALUES (37,1,'433411-Royalty-Free-RF-Clipart-Illustration-Of-Coloring-Page-Line-Art-Of-A-New-Year-Woman-Popping-Open-A-Bottle-Of-Champagne.jpg',?,1000);
