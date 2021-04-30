<?php

$host = '';
$dbname = '';
$username = '';
$password = '';//если пароля нет, отставить поле пустым

$dsn = "pgsql:host=$host;dbname=$dbname";
$opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$db = new PDO($dsn, $username, $password, $opt);

$db->query("
create table if not exists arduino_name
(
	id serial not null
		constraint monitor_pk
			primary key,
	name varchar(255)
);
");

$db->query("
create table if not exists arduino_control_param
(
	arduino_id integer
		constraint arduino_control_param_arduino_name_id_fk
			references arduino_name,
	param_name varchar(255),
	param_type varchar(255),
	param_value integer,
	constraint arduino_control_param_pk
		unique (arduino_id, param_name)
);
");

$db->query("
create table if not exists arduino_param
(
	arduino_id integer
		constraint table_name_arduino_name_id_fk
			references arduino_name,
	param_name varchar(255),
	param_value integer,
	constraint arduino_param_pk
		unique (arduino_id, param_name)
);

create index if not exists table_name_arduino_id_index
	on arduino_param (arduino_id);
");
