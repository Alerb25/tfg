--
-- PostgreSQL database dump
--

-- Dumped from database version 15.13
-- Dumped by pg_dump version 15.13

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: compartir; Type: TABLE; Schema: public; Owner: proyecto
--

CREATE TABLE public.compartir (
    id_share integer NOT NULL,
    id_user integer,
    id_notes integer,
    permisos character varying(30)
);


ALTER TABLE public.compartir OWNER TO proyecto;

--
-- Name: compartir_id_share_seq; Type: SEQUENCE; Schema: public; Owner: proyecto
--

CREATE SEQUENCE public.compartir_id_share_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.compartir_id_share_seq OWNER TO proyecto;

--
-- Name: compartir_id_share_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: proyecto
--

ALTER SEQUENCE public.compartir_id_share_seq OWNED BY public.compartir.id_share;


--
-- Name: etiqueta; Type: TABLE; Schema: public; Owner: proyecto
--

CREATE TABLE public.etiqueta (
    id_tag integer NOT NULL,
    nombre character varying(30),
    id_notes integer
);


ALTER TABLE public.etiqueta OWNER TO proyecto;

--
-- Name: etiqueta_id_tag_seq; Type: SEQUENCE; Schema: public; Owner: proyecto
--

CREATE SEQUENCE public.etiqueta_id_tag_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.etiqueta_id_tag_seq OWNER TO proyecto;

--
-- Name: etiqueta_id_tag_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: proyecto
--

ALTER SEQUENCE public.etiqueta_id_tag_seq OWNED BY public.etiqueta.id_tag;


--
-- Name: nota; Type: TABLE; Schema: public; Owner: proyecto
--

CREATE TABLE public.nota (
    id_notes integer NOT NULL,
    id_user integer,
    contenido character varying(300),
    titulo character varying(300),
    fecha_creado date,
    fecha_editado date
);


ALTER TABLE public.nota OWNER TO proyecto;

--
-- Name: nota_id_notes_seq; Type: SEQUENCE; Schema: public; Owner: proyecto
--

CREATE SEQUENCE public.nota_id_notes_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.nota_id_notes_seq OWNER TO proyecto;

--
-- Name: nota_id_notes_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: proyecto
--

ALTER SEQUENCE public.nota_id_notes_seq OWNED BY public.nota.id_notes;


--
-- Name: usuario; Type: TABLE; Schema: public; Owner: proyecto
--

CREATE TABLE public.usuario (
    id_user integer NOT NULL,
    nombre character varying(30),
    password character varying(30),
    mail character varying(30),
    p_apellido character varying(30),
    s_apellido character varying(30)
);


ALTER TABLE public.usuario OWNER TO proyecto;

--
-- Name: usuario_id_user_seq; Type: SEQUENCE; Schema: public; Owner: proyecto
--

CREATE SEQUENCE public.usuario_id_user_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.usuario_id_user_seq OWNER TO proyecto;

--
-- Name: usuario_id_user_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: proyecto
--

ALTER SEQUENCE public.usuario_id_user_seq OWNED BY public.usuario.id_user;


--
-- Name: compartir id_share; Type: DEFAULT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.compartir ALTER COLUMN id_share SET DEFAULT nextval('public.compartir_id_share_seq'::regclass);


--
-- Name: etiqueta id_tag; Type: DEFAULT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.etiqueta ALTER COLUMN id_tag SET DEFAULT nextval('public.etiqueta_id_tag_seq'::regclass);


--
-- Name: nota id_notes; Type: DEFAULT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.nota ALTER COLUMN id_notes SET DEFAULT nextval('public.nota_id_notes_seq'::regclass);


--
-- Name: usuario id_user; Type: DEFAULT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.usuario ALTER COLUMN id_user SET DEFAULT nextval('public.usuario_id_user_seq'::regclass);


--
-- Data for Name: compartir; Type: TABLE DATA; Schema: public; Owner: proyecto
--

COPY public.compartir (id_share, id_user, id_notes, permisos) FROM stdin;
\.


--
-- Data for Name: etiqueta; Type: TABLE DATA; Schema: public; Owner: proyecto
--

COPY public.etiqueta (id_tag, nombre, id_notes) FROM stdin;
\.


--
-- Data for Name: nota; Type: TABLE DATA; Schema: public; Owner: proyecto
--

COPY public.nota (id_notes, id_user, contenido, titulo, fecha_creado, fecha_editado) FROM stdin;
\.


--
-- Data for Name: usuario; Type: TABLE DATA; Schema: public; Owner: proyecto
--

COPY public.usuario (id_user, nombre, password, mail, p_apellido, s_apellido) FROM stdin;
1	Lucas	123456	lucas@gmail.com	Lopez	calatapito
\.


--
-- Name: compartir_id_share_seq; Type: SEQUENCE SET; Schema: public; Owner: proyecto
--

SELECT pg_catalog.setval('public.compartir_id_share_seq', 1, false);


--
-- Name: etiqueta_id_tag_seq; Type: SEQUENCE SET; Schema: public; Owner: proyecto
--

SELECT pg_catalog.setval('public.etiqueta_id_tag_seq', 1, false);


--
-- Name: nota_id_notes_seq; Type: SEQUENCE SET; Schema: public; Owner: proyecto
--

SELECT pg_catalog.setval('public.nota_id_notes_seq', 1, false);


--
-- Name: usuario_id_user_seq; Type: SEQUENCE SET; Schema: public; Owner: proyecto
--

SELECT pg_catalog.setval('public.usuario_id_user_seq', 1, true);


--
-- Name: compartir compartir_pkey; Type: CONSTRAINT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.compartir
    ADD CONSTRAINT compartir_pkey PRIMARY KEY (id_share);


--
-- Name: etiqueta etiqueta_pkey; Type: CONSTRAINT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.etiqueta
    ADD CONSTRAINT etiqueta_pkey PRIMARY KEY (id_tag);


--
-- Name: nota nota_pkey; Type: CONSTRAINT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.nota
    ADD CONSTRAINT nota_pkey PRIMARY KEY (id_notes);


--
-- Name: usuario uk_mail; Type: CONSTRAINT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT uk_mail UNIQUE (mail);


--
-- Name: usuario usuario_pkey; Type: CONSTRAINT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_pkey PRIMARY KEY (id_user);


--
-- Name: etiqueta etiqueta_id_notes_fkey; Type: FK CONSTRAINT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.etiqueta
    ADD CONSTRAINT etiqueta_id_notes_fkey FOREIGN KEY (id_notes) REFERENCES public.nota(id_notes) ON DELETE CASCADE;


--
-- Name: compartir fk_notes; Type: FK CONSTRAINT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.compartir
    ADD CONSTRAINT fk_notes FOREIGN KEY (id_notes) REFERENCES public.nota(id_notes);


--
-- Name: nota fk_user; Type: FK CONSTRAINT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.nota
    ADD CONSTRAINT fk_user FOREIGN KEY (id_user) REFERENCES public.usuario(id_user);


--
-- Name: compartir fk_user; Type: FK CONSTRAINT; Schema: public; Owner: proyecto
--

ALTER TABLE ONLY public.compartir
    ADD CONSTRAINT fk_user FOREIGN KEY (id_user) REFERENCES public.usuario(id_user);


--
-- PostgreSQL database dump complete
--

