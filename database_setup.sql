CREATE DATABASE IF NOT EXISTS wheresbill;

USE wheresbill;

CREATE TABLE IF NOT EXISTS pedutoSchedule (
  title TEXT,
  start TIME,
  end TIME,
  location TEXT,
  date DATE,
  published DATE
);