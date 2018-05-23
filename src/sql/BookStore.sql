CREATE TABLE BOOK(
	ISBN				VarChar(30)		NOT NULL, 
    AuthorID 			Int     		NOT NULL,
    Title				VarChar(100)	NOT NULL,
    PublisherID			Int		        NOT NULL,
    PublicationDate		Date    		NOT NULL,
    PageCount			Int				NULL,
    Description			VarChar(1000)	NULL,
    Price				Double			NULL,
    CONSTRAINT			BOOK_PK 		PRIMARY KEY(ISBN)
    );
    
CREATE TABLE AUTHOR(
    AuthorID 			Int     		NOT NULL,
    FirstName			VarChar(30)		NOT NULL,
    LastName			VarChar(30)		NOT NULL,
    CONSTRAINT			AUTHOR_PK 		PRIMARY KEY(AuthorID)
	);
    
CREATE TABLE PUBLISHER(
    PublisherID  		Int     		NOT NULL,		
    Name				VarChar(100)	NULL,
    Address				VarChar(200)	NULL,
    CONSTRAINT			PUBL_PK 		PRIMARY KEY(PublisherID)
	);
    
CREATE TABLE GENRE(
    GenreID				Int     		NOT NULL,
    Name				VarChar(60)		NOT NULL,
	CONSTRAINT			GENRE_PK 		PRIMARY KEY(GenreID)
	);
    
CREATE TABLE BOOK_GENRE(
	ISBN				VarChar(30)		NOT NULL, 
    GenreID   			Int     		NOT NULL
	);
    
ALTER TABLE BOOK
  ADD CONSTRAINT			AUTHOR_FK		FOREIGN KEY(AuthorID)
							REFERENCES AUTHOR(AuthorID);
ALTER TABLE BOOK
	ADD CONSTRAINT			PUBL_FK		FOREIGN KEY(PublisherID)
							REFERENCES PUBLISHER(PublisherID);