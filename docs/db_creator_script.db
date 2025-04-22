CREATE TABLE Telepules (
    id NUMBER PRIMARY KEY,
    orszag VARCHAR2(100) NOT NULL,
    megye VARCHAR2(100),
    telepules VARCHAR2(100) NOT NULL
);

CREATE SEQUENCE telepules_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER telepules_trigger
BEFORE INSERT ON Telepules
FOR EACH ROW
BEGIN
    SELECT telepules_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/

CREATE TABLE Felhasznalo (
    felhasznalonev VARCHAR2(50) PRIMARY KEY,
    vezeteknev VARCHAR2(50) NOT NULL,
    keresztnev VARCHAR2(50) NOT NULL,
    admin NUMBER(1) DEFAULT 0 CHECK (admin IN (0, 1)),
    email VARCHAR2(100) NOT NULL,
    jelszo VARCHAR2(255) NOT NULL,
    telepules_id NUMBER,
    FOREIGN KEY (telepules_id) REFERENCES Telepules(id) ON DELETE SET NULL
);

CREATE TABLE Kep (
    id NUMBER PRIMARY KEY,
    feltoltesi_datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    cim VARCHAR2(255) NOT NULL,
    kep_binaris BLOB,
    leiras CLOB,
    felhasznalo_felhasznalonev VARCHAR2(50),
    telepules_id NUMBER,
    FOREIGN KEY (felhasznalo_felhasznalonev) REFERENCES Felhasznalo(felhasznalonev) ON DELETE CASCADE,
    FOREIGN KEY (telepules_id) REFERENCES Telepules(id) ON DELETE SET NULL
);

CREATE SEQUENCE kep_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER kep_trigger
BEFORE INSERT ON Kep
FOR EACH ROW
BEGIN
    SELECT kep_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/

CREATE TABLE Fenykepalbum (
    id NUMBER PRIMARY KEY,
    nev VARCHAR2(255) NOT NULL,
    leiras CLOB,
    letrehozasi_datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    felhasznalo_felhasznalonev VARCHAR2(50),
    FOREIGN KEY (felhasznalo_felhasznalonev) REFERENCES Felhasznalo(felhasznalonev) ON DELETE CASCADE
);

CREATE SEQUENCE fenykepalbum_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER fenykepalbum_trigger
BEFORE INSERT ON Fenykepalbum
FOR EACH ROW
BEGIN
    SELECT fenykepalbum_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/

CREATE TABLE Hozzaszolas (
    id NUMBER PRIMARY KEY,
    szoveg CLOB NOT NULL,
    datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    felhasznalo_felhasznalonev VARCHAR2(50),
    kep_id NUMBER,
    szulo_id NUMBER NULL,
    FOREIGN KEY (felhasznalo_felhasznalonev) REFERENCES Felhasznalo(felhasznalonev) ON DELETE CASCADE,
    FOREIGN KEY (kep_id) REFERENCES Kep(id) ON DELETE CASCADE,
    FOREIGN KEY (szulo_id) REFERENCES Hozzaszolas(id) ON DELETE CASCADE
);

CREATE SEQUENCE hozzaszolas_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER hozzaszolas_trigger
BEFORE INSERT ON Hozzaszolas
FOR EACH ROW
BEGIN
    SELECT hozzaszolas_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/

CREATE TABLE Kategoria (
    nev VARCHAR2(100) PRIMARY KEY
);

CREATE TABLE KepKategoria (
    kep_id NUMBER,
    kategoria_nev VARCHAR2(100),
    PRIMARY KEY (kep_id, kategoria_nev),
    FOREIGN KEY (kep_id) REFERENCES Kep(id) ON DELETE CASCADE,
    FOREIGN KEY (kategoria_nev) REFERENCES Kategoria(nev) ON DELETE CASCADE
);

CREATE TABLE KepFenykepalbum (
    kep_id NUMBER,
    fenykepalbum_id NUMBER,
    sorszam NUMBER NOT NULL,
    PRIMARY KEY (kep_id, fenykepalbum_id),
    FOREIGN KEY (kep_id) REFERENCES Kep(id) ON DELETE CASCADE,
    FOREIGN KEY (fenykepalbum_id) REFERENCES Fenykepalbum(id) ON DELETE CASCADE
);

CREATE TABLE Ertekeles (
    felhasznalo_felhasznalonev VARCHAR2(50),
    kep_id NUMBER,
    pontszam NUMBER,
    PRIMARY KEY (felhasznalo_felhasznalonev, kep_id),
    FOREIGN KEY (felhasznalo_felhasznalonev) REFERENCES Felhasznalo(felhasznalonev) ON DELETE CASCADE,
    FOREIGN KEY (kep_id) REFERENCES Kep(id) ON DELETE CASCADE
);
