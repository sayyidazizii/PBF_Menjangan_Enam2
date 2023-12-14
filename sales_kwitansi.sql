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
CREATE DATABASE /*!32312 IF NOT EXISTS*/`ciptapro_pbf_menjangan_enam` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `ciptapro_pbf_menjangan_enam`;

/*Table structure for table `sales_kwitansi` */

DROP TABLE IF EXISTS `sales_kwitansi`;

CREATE TABLE `sales_kwitansi` (
  `sales_kwitansi_id` int NOT NULL AUTO_INCREMENT,
  `sales_kwitansi_no` varchar(255) CHARACTER NOT NULL,
  `customer_id` int DEFAULT NULL,
  `sales_kwitansi_date` date DEFAULT NULL,
  `print_type` int DEFAULT '0',
  `data_state` int DEFAULT '0',
  `created_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

/*Data for the table `sales_kwitansi` */

/*Table structure for table `sales_kwitansi_item` */

DROP TABLE IF EXISTS `sales_kwitansi_item`;

CREATE TABLE `sales_kwitansi_item` (
  `sales_kwitansi_item_id` int NOT NULL AUTO_INCREMENT,
  `sales_kwitansi_id` int DEFAULT NULL,
  `sales_invoice_id` int DEFAULT NULL,
  `buyers_acknowledgment_id` int DEFAULT NULL,
  `checked` int DEFAULT '0',
  `created_id` int DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

/*Data for the table `sales_kwitansi_item` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
