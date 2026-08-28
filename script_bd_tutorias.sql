create database if not exists bd_tutorias; 
use bd_tutorias;

-- Trabajar todo en minusculas y colocar comentario de ser necesario.
-- Tablas sin relaciones

create table if not exists materias (
	id_materia int primary key auto_increment not null,
    nombre varchar(255) not null unique,
    descripcion varchar(255) null
);

create table if not exists rol (
	id_rol int primary key auto_increment not null,
    nombre varchar(255) not null unique,
    descripcion varchar(255) null
);

-- Tablas con relaciones

-- Tablas intermedias