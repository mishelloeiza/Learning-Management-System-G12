create database if not exists bd_tutorias; 
use bd_tutorias;

create table if not exists carreras (
    id_carrera int primary key auto_increment not null,
    nombre varchar(255) not null unique,
    descripcion varchar(255) null,
    activo boolean not null default true
);

create table if not exists roles (
    id_rol int primary key auto_increment not null,
    nombre varchar(255) not null unique,
    descripcion varchar(255) null
);

create table if not exists materias (
    id_materia int primary key auto_increment not null,
    nombre varchar(255) not null unique,
    descripcion varchar(255) null,
    activo boolean not null default true,
    id_carrera int not null,
    foreign key (id_carrera) references carreras(id_carrera)
	on update cascade on delete cascade
);

create table if not exists usuarios (
    id_usuario int primary key auto_increment not null,
    nombre varchar(255) not null,
    apellido varchar(255) not null,
    correo varchar(255) not null unique,
    telefono varchar(9) not null,
    contrasena varchar(255) not null,
    activo boolean not null default true,
    id_rol int not null,
    id_carrera int not null,
    creado_en timestamp default current_timestamp not null,
    ultima_modificacion timestamp default current_timestamp on update current_timestamp not null,
    foreign key (id_rol) references roles(id_rol)
    on update cascade on delete cascade,
    foreign key (id_carrera) references carreras(id_carrera)
    on update cascade on delete cascade
);

create table if not exists tutorias (
    id_tutoria int primary key auto_increment,
    titulo varchar(255) not null,
    descripcion varchar(255) not null,
    estado varchar(15) not null check (estado in ('activa','finalizada','cancelada','en curso')),
    fecha_inicio date not null,
    fecha_fin date not null,
    id_tutor int not null,
    id_materia int not null,
    creado_en timestamp default current_timestamp not null,
    ultima_modificacion timestamp default current_timestamp on update current_timestamp not null,
    constraint chk_fechas check (fecha_fin > fecha_inicio),
    foreign key (id_tutor) references usuarios(id_usuario)
	on update cascade on delete restrict,
    foreign key (id_materia) references materias(id_materia)
	on update cascade on delete restrict
);

create table if not exists horarios (
    id_horarios int primary key auto_increment not null,
    hora_inicio time not null,
    hora_fin time not null,
    dias_curso varchar(255) not null check (dias_curso in ('lunes','martes','miercoles','jueves','viernes','sabado','domingo')),
    estado varchar(15) not null check (estado in ('disponible','finalizado','cancelado','asignado')),
    id_tutoria int not null,
    constraint chk_horas check (hora_fin > hora_inicio),
    foreign key (id_tutoria) references tutorias(id_tutoria)
    on update cascade on delete cascade
);

create table if not exists solicitudes (
    id_solicitud int primary key auto_increment not null,
    estado varchar(10) not null check (estado in ('pendiente','aprobada','rechazada')),
    fecha_solicitud timestamp default current_timestamp not null,
    fecha_respuesta date null,
    id_horario int not null,
    id_usuario int not null,
    foreign key (id_horario) references horarios(id_horarios)
    on update cascade on delete cascade,
    foreign key (id_usuario) references usuarios(id_usuario)
    on update cascade on delete cascade
);

create or replace view usuarios_1 as select * from usuarios where id_rol = 1;
create or replace view usuarios_2 as select * from usuarios where id_rol = 2;
create or replace view usuarios_3 as select * from usuarios where id_rol = 3;
create or replace view usuarios_login as
select id_usuario, correo, contrasena, id_rol, activo from usuarios;

insert ignore into roles (id_rol, nombre, descripcion) values (1, 'Estudiante',    'Usuario de estudiantes'),
(2, 'Tutor', 'Usuario de tutores'), (3, 'Administrador', 'Usuario de Administradores');

insert ignore into carreras (id_carrera, nombre, descripcion) values (1, 'Ingenieria en sistemas', 'Carrera de ingenieria en sistemas'),
(2, 'Ingenieria industrial', 'Carrera de ingenieria industrial'), (3, 'Derecho', 'Carrera de ciencias juridicas y sociales');

select*from usuarios;