/**
 * This is the MySQL database schema for creation of the test Firebird index sources.
 */
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'composite_fk')) THEN 
        EXECUTE STATEMENT 'DROP TABLE composite_fk;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'order_item')) THEN 
        EXECUTE STATEMENT 'DROP TABLE order_item;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'order_item_with_null_fk')) THEN 
        EXECUTE STATEMENT 'DROP TABLE order_item_with_null_fk;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'item')) THEN 
        EXECUTE STATEMENT 'DROP TABLE item;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'order')) THEN 
        EXECUTE STATEMENT 'DROP TABLE "order";';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'order_with_null_fk')) THEN 
        EXECUTE STATEMENT 'DROP TABLE order_with_null_fk;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'category')) THEN 
        EXECUTE STATEMENT 'DROP TABLE category;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'customer')) THEN 
        EXECUTE STATEMENT 'DROP TABLE customer;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'profile')) THEN 
        EXECUTE STATEMENT 'DROP TABLE profile;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'type')) THEN 
        EXECUTE STATEMENT 'DROP TABLE type;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'null_values')) THEN 
        EXECUTE STATEMENT 'DROP TABLE null_values;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'negative_default_values')) THEN 
        EXECUTE STATEMENT 'DROP TABLE negative_default_values;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'constraints')) THEN 
        EXECUTE STATEMENT 'DROP TABLE constraints;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'animal')) THEN 
        EXECUTE STATEMENT 'DROP TABLE animal;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'default_pk')) THEN 
        EXECUTE STATEMENT 'DROP TABLE default_pk;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'document')) THEN 
        EXECUTE STATEMENT 'DROP TABLE document;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'comment')) THEN 
        EXECUTE STATEMENT 'DROP TABLE comment;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'animal_view')) THEN 
        EXECUTE STATEMENT 'DROP VIEW animal_view;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 't_constraints_4')) THEN 
        EXECUTE STATEMENT 'DROP TABLE t_constraints_4;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 't_constraints_3')) THEN 
        EXECUTE STATEMENT 'DROP TABLE t_constraints_3;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 't_constraints_2')) THEN 
        EXECUTE STATEMENT 'DROP TABLE t_constraints_2;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 't_constraints_1')) THEN 
        EXECUTE STATEMENT 'DROP TABLE t_constraints_1;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 't_upsert')) THEN 
        EXECUTE STATEMENT 'DROP TABLE t_upsert;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'seq_null_values_id')) THEN 
        EXECUTE STATEMENT 'DROP SEQUENCE seq_null_values_id;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'seq_animal_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR seq_animal_id;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'gen_profile_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR gen_profile_id;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'gen_customer_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR gen_customer_id;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'gen_category_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR gen_category_id;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'gen_item_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR gen_item_id;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'gen_order_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR gen_order_id;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'gen_order_with_null_fk_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR gen_order_with_null_fk_id;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'gen_document_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR gen_document_id;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'seq_comment_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR seq_comment_id;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'gen_t_upsert_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR gen_t_upsert_id;';
END;
-- SQL
CREATE TABLE constraints (
  id INTEGER NOT NULL,
  field1 varchar(255)
);
-- SQL
CREATE TABLE profile (
  id INTEGER NOT NULL,
  description varchar(128) NOT NULL,
  PRIMARY KEY (id)
);
-- SQL
CREATE GENERATOR gen_profile_id;
-- SQL
CREATE TRIGGER tr_profile FOR profile
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = GEN_ID(gen_profile_id, 1);
END
-- SQL
CREATE TABLE customer (
  id INTEGER NOT NULL,
  email varchar(128) NOT NULL,
  name varchar(128),
  address varchar(255),
  status INTEGER DEFAULT 0,
  profile_id INTEGER,
  PRIMARY KEY (id)
);
-- SQL
CREATE GENERATOR gen_customer_id;
-- SQL
CREATE TRIGGER tr_customer FOR customer
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = GEN_ID(gen_customer_id, 1);
END
-- SQL
CREATE TABLE category (
  id INTEGER NOT NULL,
  name varchar(128) NOT NULL,
  PRIMARY KEY (id)
);
-- SQL
CREATE GENERATOR gen_category_id;
-- SQL
CREATE TRIGGER tr_category FOR category
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = GEN_ID(gen_category_id, 1);
END
-- SQL
CREATE TABLE item (
  id INTEGER NOT NULL,
  name varchar(128) NOT NULL,
  category_id INTEGER NOT NULL,
  PRIMARY KEY (id)
);
-- SQL
CREATE GENERATOR gen_item_id;
-- SQL
CREATE TRIGGER tr_item FOR item
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = GEN_ID(gen_item_id, 1);
END
-- SQL
CREATE TABLE "order" (
  id INTEGER NOT NULL,
  customer_id INTEGER NOT NULL,
  created_at INTEGER NOT NULL,
  total decimal(10,0) NOT NULL,
  PRIMARY KEY (id)
);
-- SQL
CREATE GENERATOR gen_order_id;
-- SQL
CREATE TRIGGER tr_order FOR "order"
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = GEN_ID(gen_order_id, 1);
END
-- SQL
CREATE TABLE order_with_null_fk (
  id INTEGER NOT NULL,
  customer_id INTEGER,
  created_at INTEGER NOT NULL,
  total decimal(10,0) NOT NULL,
  PRIMARY KEY (id)
);
-- SQL
CREATE GENERATOR gen_order_with_null_fk_id;
-- SQL
CREATE TRIGGER tr_order_with_null_fk FOR order_with_null_fk
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = GEN_ID(gen_order_with_null_fk_id, 1);
END
-- SQL
CREATE TABLE order_item (
  order_id INTEGER NOT NULL,
  item_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL,
  subtotal decimal(10,0) NOT NULL,
  PRIMARY KEY (order_id, item_id),
  CONSTRAINT FK_single_fk_order FOREIGN KEY (order_id) REFERENCES "order" (id) ON DELETE CASCADE,
  CONSTRAINT FK_single_fk_item FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE
);
-- SQL
CREATE TABLE order_item_with_null_fk (
  order_id INTEGER,
  item_id INTEGER,
  quantity INTEGER NOT NULL,
  subtotal decimal(10,0) NOT NULL
);
-- SQL
CREATE TABLE composite_fk (
  id INT NOT NULL,
  order_id INT NOT NULL,
  item_id INT NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_composite_fk_order_item FOREIGN KEY (order_id, item_id) REFERENCES order_item (order_id, item_id) ON DELETE CASCADE
);
-- SQL
CREATE TABLE null_values (
  id INTEGER PRIMARY KEY NOT NULL,
  var1 INTEGER,
  var2 INTEGER,
  var3 INTEGER DEFAULT NULL,
  stringcol VARCHAR(32) DEFAULT NULL
);
-- SQL
CREATE SEQUENCE seq_null_values_id;
-- SQL
CREATE TRIGGER tr_null_values FOR null_values
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = NEXT VALUE FOR seq_null_values_id;
END
-- SQL
CREATE TABLE negative_default_values (
  tinyint_col smallint default '-123',
  smallint_col smallint default '-123',
  int_col integer default '-123',
  bigint_col bigint default '-123',
  float_col double precision default '-12345.6789',
  numeric_col decimal(5,2) default '-33.22'
);
-- SQL
CREATE TABLE type (
  int_col INTEGER NOT NULL,
  int_col2 INTEGER DEFAULT '1',
  tinyint_col SMALLINT DEFAULT '1',
  smallint_col SMALLINT DEFAULT '1',
  char_col char(100) NOT NULL,
  char_col2 varchar(100) DEFAULT 'something',
  char_col3 blob sub_type text,
  float_col DOUBLE PRECISION NOT NULL,
  float_col2 DOUBLE PRECISION DEFAULT '1.23',
  blob_col blob,
  numeric_col decimal(5,2) DEFAULT '33.22',
  "time" TIMESTAMP DEFAULT '2002-01-01 00:00:00' NOT NULL,
  bool_col SMALLINT NOT NULL,
  bool_col2 SMALLINT DEFAULT '1',
  ts_default TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  bit_col SMALLINT DEFAULT '130' NOT NULL

);
-- SQL
CREATE TABLE animal (
  id INTEGER NOT NULL,
  type VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
);
-- SQL
CREATE SEQUENCE seq_animal_id;
-- SQL
CREATE TRIGGER tr_animal FOR animal
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = NEXT VALUE FOR seq_animal_id;
END
-- SQL
CREATE TABLE default_pk (
  id INTEGER DEFAULT 5 NOT NULL ,
  type VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
);
-- SQL
CREATE TABLE document (
  id INTEGER NOT NULL,
  title VARCHAR(255) NOT NULL,
  content varchar(255),
  version INTEGER DEFAULT '0' NOT NULL ,
  PRIMARY KEY (id)
);
-- SQL
CREATE GENERATOR gen_document_id;
-- SQL
CREATE TRIGGER tr_document FOR document
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = GEN_ID(gen_document_id, 1);
END
-- SQL
CREATE TABLE comment (
  id INTEGER NOT NULL,
  type VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
);
-- SQL
CREATE SEQUENCE seq_comment_id;
-- SQL
CREATE TRIGGER tr_comment FOR comment
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = NEXT VALUE FOR seq_comment_id;
END
-- SQL
CREATE VIEW animal_view AS SELECT * FROM animal;
-- SQL
EXECUTE block AS BEGIN
    INSERT INTO animal (type) VALUES ('yiiunit\data\ar\Cat');
    INSERT INTO animal (type) VALUES ('yiiunit\data\ar\Dog');
END;
-- SQL
EXECUTE block AS
BEGIN
    INSERT INTO profile (description) VALUES ('profile customer 1');
    INSERT INTO profile (description) VALUES ('profile customer 3');
END;
-- SQL
EXECUTE block AS
BEGIN
    INSERT INTO customer (email, name, address, status, profile_id) VALUES ('user1@example.com', 'user1', 'address1', 1, 1);
    INSERT INTO customer (email, name, address, status) VALUES ('user2@example.com', 'user2', 'address2', 1);
    INSERT INTO customer (email, name, address, status, profile_id) VALUES ('user3@example.com', 'user3', 'address3', 2, 2);
END;
-- SQL
EXECUTE block AS
BEGIN
    INSERT INTO category (name) VALUES ('Books');
    INSERT INTO category (name) VALUES ('Movies');
END;
-- SQL
EXECUTE block AS
BEGIN
    INSERT INTO item (name, category_id) VALUES ('Agile Web Application Development with Yii1.1 and PHP5', 1);
    INSERT INTO item (name, category_id) VALUES ('Yii 1.1 Application Development Cookbook', 1);
    INSERT INTO item (name, category_id) VALUES ('Ice Age', 2);
    INSERT INTO item (name, category_id) VALUES ('Toy Story', 2);
    INSERT INTO item (name, category_id) VALUES ('Cars', 2);
END;
-- SQL
EXECUTE block AS
BEGIN
    INSERT INTO "order" (customer_id, created_at, total) VALUES (1, 1325282384, 110.0);
    INSERT INTO "order" (customer_id, created_at, total) VALUES (2, 1325334482, 33.0);
    INSERT INTO "order" (customer_id, created_at, total) VALUES (2, 1325502201, 40.0);
END;
-- SQL
EXECUTE block AS
BEGIN
    INSERT INTO order_with_null_fk (customer_id, created_at, total) VALUES (1, 1325282384, 110.0);
    INSERT INTO order_with_null_fk (customer_id, created_at, total) VALUES (2, 1325334482, 33.0);
    INSERT INTO order_with_null_fk (customer_id, created_at, total) VALUES (2, 1325502201, 40.0);
END;
-- SQL
EXECUTE block AS
BEGIN
    INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
    INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
    INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
    INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
    INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
    INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);
END;
-- SQL
EXECUTE block AS
BEGIN
    INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
    INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
    INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
    INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
    INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
    INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);
END;
-- SQL
EXECUTE block AS
BEGIN
    INSERT INTO document (title, content, version) VALUES ('Yii 2.0 guide', 'This is Yii 2.0 guide', 0);
END;
-- SQL
/* bit test, see https://github.com/yiisoft/yii2/issues/9006 */
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'bit_values')) THEN 
        EXECUTE STATEMENT 'DROP TABLE bit_values;';
END;
-- SQL
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$generators WHERE LOWER(rdb$generator_name) = 'gen_bit_values_id')) THEN 
        EXECUTE STATEMENT 'DROP GENERATOR gen_bit_values_id;';
END;
-- SQL
CREATE TABLE bit_values (
  id INTEGER NOT NULL,
  val SMALLINT NOT NULL,
  PRIMARY KEY (id)
);
-- SQL
CREATE GENERATOR gen_bit_values_id;
-- SQL
CREATE TRIGGER tr_bit_values FOR bit_values
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = GEN_ID(gen_bit_values_id, 1);
END;
-- SQL
EXECUTE block AS
BEGIN
    INSERT INTO bit_values (id, val) VALUES (1, 0);
    INSERT INTO bit_values (id, val) VALUES (2, 1);
END;
-- SQL
/* unique indexes test */
EXECUTE block AS
BEGIN
    IF (EXISTS(SELECT 1 FROM rdb$relations WHERE LOWER(rdb$relation_name) = 'unique_values')) THEN 
        EXECUTE STATEMENT 'DROP TABLE unique_values;';
END;
-- SQL
CREATE TABLE unique_values (
  a INTEGER NOT NULL,
  b INTEGER NOT NULL,
  c INTEGER NOT NULL
);
-- SQL
CREATE UNIQUE INDEX uniqueA ON unique_values (a);
-- SQL
CREATE UNIQUE INDEX uniqueB ON unique_values (b);
-- SQL
CREATE UNIQUE INDEX uniqueBC ON unique_values (b, c);
-- SQL
CREATE UNIQUE INDEX uniqueABC ON unique_values (a, b, c);
-- SQL
CREATE TABLE t_constraints_1
(
    c_id INT NOT NULL PRIMARY KEY,
    c_not_null INT NOT NULL,
    c_check VARCHAR(255) CHECK (c_check <> ''),
    c_unique INT NOT NULL,
    c_default INT DEFAULT 0 NOT NULL,
    CONSTRAINT cn_unique UNIQUE (c_unique)
);
-- SQL
CREATE TABLE t_constraints_2
(
    c_id_1 INT NOT NULL,
    c_id_2 INT NOT NULL,
    c_index_1 INT,
    c_index_2_1 INT,
    c_index_2_2 INT,
    CONSTRAINT cn_constraints_2_multi UNIQUE (c_index_2_1, c_index_2_2),
    CONSTRAINT cn_pk PRIMARY KEY (c_id_1, c_id_2)
);
-- SQL
CREATE INDEX cn_constraints_2_single ON t_constraints_2 (c_index_1);
-- SQL
CREATE TABLE t_constraints_3
(
    c_id INT NOT NULL,
    c_fk_id_1 INT NOT NULL,
    c_fk_id_2 INT NOT NULL,
    CONSTRAINT cn_constraints_3 FOREIGN KEY (c_fk_id_1, c_fk_id_2) REFERENCES T_constraints_2 (c_id_1, c_id_2) ON DELETE CASCADE ON UPDATE CASCADE
);
-- SQL
CREATE TABLE t_constraints_4
(
    c_id INT NOT NULL PRIMARY KEY,
    c_col_1 INT,
    c_col_2 INT NOT NULL,
    CONSTRAINT cn_constraints_4 UNIQUE (c_col_1, c_col_2)
);
-- SQL
CREATE TABLE t_upsert
(
    id INTEGER NOT NULL PRIMARY KEY,
    ts INT,
    email VARCHAR(128) NOT NULL UNIQUE,
    recovery_email VARCHAR(128),
    address blob sub_type text,
    status SMALLINT DEFAULT 0 NOT NULL,
    orders INT DEFAULT 0 NOT NULL,
    profile_id INT,
    UNIQUE (email, recovery_email)
);
-- SQL
CREATE GENERATOR gen_t_upsert_id;
-- SQL
CREATE TRIGGER tr_t_upsert FOR t_upsert
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.ID is NULL) then NEW.ID = GEN_ID(gen_t_upsert_id, 1);
END;
