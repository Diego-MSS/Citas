set names utf8mb4;
set FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS CITA;
DROP TABLE IF EXISTS USUARIO;
DROP TABLE IF EXISTS SLOTS;

Create table USUARIO( 
id int auto_increment primary key, 
login varchar(250), 
pass varchar(250), 
nombre varchar(250));
create table SLOTS( 
id int auto_increment primary key, 
hora time);
create table CITA( 
id int auto_increment primary key, 
fecha date, 
hora int,
asunto Varchar(250) 
usuario int, 
estado enum(’RESERVADA’, ‘CONFIRMADA’,CANCELADA’,’FINALIZADA’) DEFAULT ‘RESERVADA’,
foreign key (hora) references SLOTS (id), 
foreign key (usuario) references USUARIO (id));