CREATE TABLE "users" (
	"id"	INTEGER NOT NULL,
	"username"	TEXT NOT NULL UNIQUE,
	"email"	TEXT NOT NULL,
	"pass_hash"	TEXT NOT NULL,
	"otp_secret"	TEXT NOT NULL,
	"date_created"	TEXT NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
);