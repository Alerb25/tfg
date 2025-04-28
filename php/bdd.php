-- Estructura completa de la base de datos según el diagrama ER

-- Tabla Usuario
CREATE TABLE Usuario (
    Id_User SERIAL PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Mail VARCHAR(255) NOT NULL UNIQUE,
    P_Apellido VARCHAR(100),
    S_Apellido VARCHAR(100),
    Fecha_Registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla Nota
CREATE TABLE Nota (
    Id_Note SERIAL PRIMARY KEY,
    Contenido TEXT NOT NULL,
    Fecha_Creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Fecha_Editado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Id_User INTEGER NOT NULL,
    FOREIGN KEY (Id_User) REFERENCES Usuario(Id_User) ON DELETE CASCADE
);

-- Tabla Etiqueta
CREATE TABLE Etiqueta (
    Id_Tag SERIAL PRIMARY KEY,
    Nombre VARCHAR(50) NOT NULL,
    Id_User INTEGER NOT NULL,
    FOREIGN KEY (Id_User) REFERENCES Usuario(Id_User) ON DELETE CASCADE
);

-- Tabla de relación Nota-Etiqueta (Tiene)
CREATE TABLE Nota_Etiqueta (
    Id_Note INTEGER NOT NULL,
    Id_Tag INTEGER NOT NULL,
    PRIMARY KEY (Id_Note, Id_Tag),
    FOREIGN KEY (Id_Note) REFERENCES Nota(Id_Note) ON DELETE CASCADE,
    FOREIGN KEY (Id_Tag) REFERENCES Etiqueta(Id_Tag) ON DELETE CASCADE
);

-- Tabla para compartir notas
CREATE TABLE Compartir (
    Id_Share SERIAL PRIMARY KEY,
    Id_Note INTEGER NOT NULL,
    Id_User INTEGER NOT NULL, -- Usuario con quien se comparte
    Permisos VARCHAR(20) NOT NULL, -- 'lectura', 'edición', etc.
    Fecha_Compartido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Id_Note) REFERENCES Nota(Id_Note) ON DELETE CASCADE,
    FOREIGN KEY (Id_User) REFERENCES Usuario(Id_User) ON DELETE CASCADE,
    UNIQUE (Id_Note, Id_User) -- Evita compartir la misma nota más de una vez con el mismo usuario
);

-- Tabla para archivos adjuntos a las notas
CREATE TABLE Archivo (
    Id_Archivo SERIAL PRIMARY KEY,
    Id_Note INTEGER NOT NULL,
    Nombre_Archivo VARCHAR(255) NOT NULL,
    Ruta_Archivo VARCHAR(255) NOT NULL,
    Tipo_Archivo VARCHAR(50) NOT NULL,
    Tamaño_Archivo INTEGER NOT NULL, -- en bytes
    Fecha_Subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Id_Note) REFERENCES Nota(Id_Note) ON DELETE CASCADE
);

-- Índices para optimizar consultas
CREATE INDEX idx_nota_usuario ON Nota(Id_User);
CREATE INDEX idx_nota_fecha ON Nota(Fecha_Editado);
CREATE INDEX idx_etiqueta_usuario ON Etiqueta(Id_User);
CREATE INDEX idx_compartir_usuario ON Compartir(Id_User);
CREATE INDEX idx_compartir_nota ON Compartir(Id_Note);
CREATE INDEX idx_archivo_nota ON Archivo(Id_Note);