/*
SQLyog Professional v13.1.1 (64 bit)
MySQL - 8.0.30 : Database - ciptapro_pbf_menjangan_enam
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`ciptapro_pbf_menjangan_enam` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `ciptapro_pbf_menjangan_enam`;

/*Table structure for table `sales_order_return` */

DROP TABLE IF EXISTS `sales_order_return`;

CREATE TABLE `sales_order_return` (
  `sales_order_return_id` bigint NOT NULL AUTO_INCREMENT,
  `sales_delivery_note_id` int DEFAULT NULL,
  `sales_delivery_order_id` int DEFAULT NULL,
  `sales_order_id` int DEFAULT NULL,
  `sales_invoice_id` int DEFAULT NULL,
  `warehouse_id` bigint DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `sales_order_return_no` varchar(200) CHARACTER   DEFAULT NULL,
  `no_retur_barang` varchar(255) CHARACTER  DEFAULT NULL,
  `nota_retur_pajak` varchar(255) CHARACTER  DEFAULT NULL,
  `barang_kembali` int DEFAULT '0',
  `sales_order_return_date` date DEFAULT NULL,
  `sales_order_return_remark` text CHARACTER ,
  `data_state` int DEFAULT '0',
  `created_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sales_order_return_id`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb3;

/*Data for the table `sales_order_return` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
