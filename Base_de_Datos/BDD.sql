-- Database
create database proyecto;
create user proyecto with password 'proyecto';
grant all privileges on database proyecto to proyecto;
\c proyecto
GRANT CONNECT ON DATABASE proyecto TO proyecto;
GRANT pg_read_all_data TO proyecto;
GRANT pg_write_all_data TO proyecto;
--Tables

--Usuario
create table usuario (id_User SERIAL, nombre varchar(30), Password varchar(30), mail varchar(30), p_apellido varchar(30), s_apellido varchar(30));
alter table usuario add primary key (id_User);
alter table usuario add constraint uk_mail unique (mail);

--nota
create table nota (id_Notes SERIAL, id_User int, contenido varchar(300), titulo varchar(300), fecha_creado date, fecha_editado date);
alter table nota add primary key (id_Notes);
alter table nota add constraint fk_user foreign key (id_User) References usuario (id_User);
 
--Compartir
create table compartir (id_Share SERIAL, id_User int, id_Notes int, permisos varchar(30) );
alter table compartir add primary key (id_Share);
alter table compartir add constraint fk_notes foreign key (id_Notes) References nota (id_Notes);
alter table compartir add constraint fk_user foreign key (id_User) References usuario (id_User);
 
--Etiqueta
create table etiqueta (id_Tag SERIAL, nombre varchar(30), id_notes INTEGER REFERENCES nota(id_notes) ON DELETE CASCADE);
alter table etiqueta add primary key (id_Tag);