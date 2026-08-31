create database if not exists bd_tutorias;
use bd_tutorias;

-- tablas sin relaciones
create table if not exists materias (
    id_materia int primary key auto_increment not null,
    nombre varchar(255) not null unique,
    descripcion varchar(255) null
);

create table if not exists roles (
    id_rol int primary key auto_increment not null,
    nombre varchar(255) not null unique,
    descripcion varchar(255) null
);

create table if not exists tutores (
    id_tutor int primary key auto_increment not null,
    nombre varchar(255) not null,
    apellido varchar(255) not null,
    correo varchar(255) not null,
    telefono varchar(255) not null
);

-- tablas con relaciones
create table if not exists usuarios (
    id_usuario int primary key auto_increment not null,
    nombre varchar(255) not null,
    apellido varchar(255) not null,
    correo varchar(255) not null unique,
    telefono varchar(9) not null,
    contrasena varchar(255) not null,
    id_rol int not null,
    creado_en timestamp default current_timestamp not null,
    ultima_modificacion timestamp default current_timestamp on update current_timestamp not null,
    foreign key (id_rol) references roles(id_rol)
        on update cascade
        on delete cascade
);

create table if not exists tutorias (
    id_tutoria int primary key auto_increment not null,
    titulo varchar(255) not null,
    descripcion varchar(255) not null,
    estado varchar(15) not null check (estado in ('activa','finalizada','cancelada','en curso')),
    fecha_inicio date not null,
    fecha_fin date not null check (fecha_fin > fecha_inicio),
    id_tutor int not null,
    id_materia int not null,
    creado_en timestamp default current_timestamp not null,
    ultima_modificacion timestamp default current_timestamp on update current_timestamp not null,
    foreign key (id_tutor) references tutores(id_tutor)
        on update cascade
        on delete cascade,
    foreign key (id_materia) references materias(id_materia)
        on update cascade
        on delete cascade
);

create table if not exists horarios (
    id_horarios int primary key auto_increment not null,
    hora_inicio time not null,
    hora_fin time not null check (hora_fin > hora_inicio),
    dias_curso varchar(255) not null check (dias_curso in ('lunes','martes','miercoles','jueves','viernes','sabado','domingo')),
    estado varchar(15) not null check (estado in ('disponible','finalizado','cancelado','asignado')),
    id_tutoria int not null,
    foreign key (id_tutoria) references tutorias(id_tutoria)
        on update cascade
        on delete cascade
);

-- tablas intermedias
create table if not exists especialidades (
    id_especialidad int primary key auto_increment not null,
    id_tutor int not null,
    id_materia int not null,
    foreign key (id_tutor) references tutores(id_tutor)
        on update cascade
        on delete cascade,
    foreign key (id_materia) references materias(id_materia)
        on update cascade
        on delete cascade,
    unique key uq_tutor_materia (id_tutor, id_materia)
);

create table if not exists solicitudes (
    id_solicitud int primary key auto_increment not null,
    estado varchar(10) not null check (estado in ('pendiente','aprobada','rechazada')),
    fecha_solicitud timestamp default current_timestamp not null,
    fecha_respuesta date null,
    id_horario int not null,
    id_usuario int not null,
    foreign key (id_horario) references horarios(id_horarios)
        on update cascade
        on delete cascade,
    foreign key (id_usuario) references usuarios(id_usuario)
        on update cascade
        on delete cascade
);