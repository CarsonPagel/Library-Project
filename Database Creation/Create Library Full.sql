-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 09, 2025 at 10:01 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `Author`
--

CREATE TABLE `Author` (
  `AuthorID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Author`
--

INSERT INTO `Author` (`AuthorID`, `Name`) VALUES
(1, 'J.K. Rowling'),
(2, 'George R.R. Martin'),
(3, 'J.R.R. Tolkien'),
(4, 'Stephen King'),
(5, 'Agatha Christie'),
(6, 'Isaac Asimov'),
(7, 'Margaret Atwood'),
(8, 'Haruki Murakami');

-- --------------------------------------------------------

--
-- Table structure for table `Book`
--

CREATE TABLE `Book` (
  `ISBN` char(13) NOT NULL,
  `DocID` int(11) NOT NULL,
  `AuthorID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Book`
--

INSERT INTO `Book` (`ISBN`, `DocID`, `AuthorID`) VALUES
('9780062073556', 10, 5),
('9780062693662', 9, 5),
('9780261103368', 4, 3),
('9780261103577', 3, 3),
('9780385333312', 7, 4),
('9780385490818', 13, 7),
('9780441172719', 16, 1),
('9780553103540', 5, 2),
('9780553108033', 6, 2),
('9780553293357', 12, 6),
('9780553295209', 11, 6),
('9780553418026', 17, 1),
('9780593135204', 18, 1),
('9780670813650', 8, 4),
('9780735224629', 20, 1),
('9780747532699', 1, 1),
('9780747538493', 2, 1),
('9780857514141', 15, 8),
('9781501267352', 19, 1),
('9784101001019', 14, 8);

-- --------------------------------------------------------

--
-- Table structure for table `Borrows`
--

CREATE TABLE `Borrows` (
  `DocID` int(11) NOT NULL,
  `CopyNum` smallint(6) NOT NULL,
  `UserID` int(11) NOT NULL,
  `BorrowDate` date NOT NULL,
  `ReturnDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Borrows`
--

INSERT INTO `Borrows` (`DocID`, `CopyNum`, `UserID`, `BorrowDate`, `ReturnDate`) VALUES
(1, 2, 2, '2025-11-20', '2025-12-04'),
(2, 2, 4, '2025-11-25', '2025-12-09'),
(3, 3, 5, '2025-09-01', '2025-09-15'),
(5, 2, 3, '2025-11-15', '2025-11-29'),
(6, 2, 4, '2025-11-22', '2025-12-06'),
(8, 1, 5, '2025-11-01', '2025-11-15'),
(13, 1, 4, '2025-11-18', '2025-12-02'),
(13, 3, 5, '2025-11-20', '2025-12-04'),
(17, 1, 2, '2025-11-10', '2025-11-24'),
(19, 1, 3, '2025-10-15', '2025-10-29');

--
-- Triggers `Borrows`
--
DELIMITER $$
CREATE TRIGGER `Borrow Limit` BEFORE INSERT ON `Borrows` FOR EACH ROW BEGIN
    DECLARE current_borrow_count INT;

    SELECT COUNT(*)
    INTO current_borrow_count
    FROM Borrows
    WHERE UserID = NEW.UserID;

    IF current_borrow_count >= 5 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Borrow limit exceeded: A member may borrow at most 5 copies';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Copy`
--

CREATE TABLE `Copy` (
  `DocID` int(11) NOT NULL,
  `BranchID` int(11) NOT NULL,
  `CopyNum` smallint(6) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Copy`
--

INSERT INTO `Copy` (`DocID`, `BranchID`, `CopyNum`, `UserID`, `Date`) VALUES
(1, 1, 1, 2, '2025-01-15'),
(1, 1, 2, NULL, '2025-01-15'),
(1, 1, 3, 3, '2025-01-15'),
(2, 1, 1, NULL, '2025-01-16'),
(2, 1, 2, 4, '2025-01-16'),
(3, 2, 1, NULL, '2025-01-17'),
(3, 2, 2, NULL, '2025-01-17'),
(3, 2, 3, 5, '2025-01-17'),
(4, 2, 1, NULL, '2025-01-18'),
(4, 2, 2, 2, '2025-01-18'),
(5, 1, 1, NULL, '2025-01-19'),
(5, 1, 2, 3, '2025-01-19'),
(5, 1, 3, NULL, '2025-01-19'),
(6, 1, 1, NULL, '2025-01-20'),
(6, 1, 2, 4, '2025-01-20'),
(7, 2, 1, NULL, '2025-01-21'),
(7, 2, 2, NULL, '2025-01-21'),
(8, 2, 1, 5, '2025-01-22'),
(8, 2, 2, NULL, '2025-01-22'),
(8, 2, 3, NULL, '2025-01-22'),
(9, 1, 1, NULL, '2025-01-23'),
(9, 1, 2, NULL, '2025-01-23'),
(10, 1, 1, 3, '2025-01-24'),
(10, 1, 2, NULL, '2025-01-24'),
(11, 2, 1, NULL, '2025-01-25'),
(11, 2, 2, 2, '2025-01-25'),
(11, 2, 3, NULL, '2025-01-25'),
(12, 2, 1, NULL, '2025-01-26'),
(12, 2, 2, NULL, '2025-01-26'),
(13, 1, 1, 4, '2025-01-27'),
(13, 1, 2, NULL, '2025-01-27'),
(13, 1, 3, 5, '2025-01-27'),
(14, 2, 1, NULL, '2025-01-28'),
(14, 2, 2, NULL, '2025-01-28'),
(15, 2, 1, NULL, '2025-01-29'),
(15, 2, 2, NULL, '2025-01-29'),
(16, 1, 1, NULL, '2025-01-30'),
(16, 1, 2, NULL, '2025-01-30'),
(16, 1, 3, NULL, '2025-01-30'),
(17, 1, 1, 2, '2025-01-31'),
(17, 1, 2, NULL, '2025-01-31'),
(18, 2, 1, NULL, '2025-02-01'),
(18, 2, 2, NULL, '2025-02-01'),
(19, 1, 1, NULL, '2025-02-02'),
(19, 1, 2, NULL, '2025-02-02'),
(19, 1, 3, 3, '2025-02-02'),
(20, 2, 1, NULL, '2025-02-03'),
(20, 2, 2, 4, '2025-02-03');

-- --------------------------------------------------------

--
-- Table structure for table `Director`
--

CREATE TABLE `Director` (
  `DirectorID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Director`
--

INSERT INTO `Director` (`DirectorID`, `Name`) VALUES
(1, 'Christopher Columbus'),
(2, 'Steven Spielberg'),
(3, 'Peter Jackson'),
(4, 'James Cameron'),
(5, 'Denis Villeneuve'),
(6, 'Christopher Nolan');

-- --------------------------------------------------------

--
-- Table structure for table `Document`
--

CREATE TABLE `Document` (
  `DocID` int(11) NOT NULL,
  `Title` varchar(1000) NOT NULL,
  `Publisher` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Document`
--

INSERT INTO `Document` (`DocID`, `Title`, `Publisher`) VALUES
(1, 'Harry Potter and the Philosopher\'s Stone', 'Bloomsbury Publishing'),
(2, 'Harry Potter and the Chamber of Secrets', 'Bloomsbury Publishing'),
(3, 'The Hobbit', 'Allen & Unwin'),
(4, 'The Lord of the Rings: The Fellowship of the Ring', 'Allen & Unwin'),
(5, 'A Game of Thrones', 'Bantam Books'),
(6, 'A Clash of Kings', 'Bantam Books'),
(7, 'The Shining', 'Doubleday'),
(8, 'It', 'Viking'),
(9, 'Murder on the Orient Express', 'Collins Crime Club'),
(10, 'Death on the Nile', 'Collins Crime Club'),
(11, 'Foundation', 'Gnome Press'),
(12, 'Foundation and Empire', 'Gnome Press'),
(13, 'The Handmaid\'s Tale', 'McClelland and Stewart'),
(14, 'Norwegian Wood', 'Kodansha'),
(15, '1Q84', 'Shinchosha'),
(16, 'Dune', 'Chilton Books'),
(17, 'The Martian', 'Crown'),
(18, 'Project Hail Mary', 'Ballantine Books'),
(19, 'The Silent Patient', 'Verity'),
(20, 'Where the Crawdads Sing', 'Penguin'),
(21, 'Harry Potter and the Philosopher\'s Stone (Film)', 'Warner Bros.'),
(22, 'The Lord of the Rings: The Fellowship of the Ring (Film)', 'New Line Cinema'),
(23, 'Dune (2021 Film)', 'Warner Bros.'),
(24, 'Nature Science Review', 'Nature Publishing Group'),
(25, 'Scientific American', 'Springer Nature'),
(26, 'The Computer Journal', 'Oxford University Press');

-- --------------------------------------------------------

--
-- Table structure for table `DVD`
--

CREATE TABLE `DVD` (
  `DVDID` int(11) NOT NULL,
  `DocID` int(11) NOT NULL,
  `DirectorID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `DVD`
--

INSERT INTO `DVD` (`DVDID`, `DocID`, `DirectorID`) VALUES
(1, 21, 1),
(2, 22, 3),
(3, 23, 5);

-- --------------------------------------------------------

--
-- Table structure for table `Editor`
--

CREATE TABLE `Editor` (
  `EditorID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Editor`
--

INSERT INTO `Editor` (`EditorID`, `Name`) VALUES
(1, 'Donald E. Knuth'),
(2, 'Brian W. Kernighan'),
(3, 'Bjarne Stroustrup'),
(4, 'Guido van Rossum'),
(5, 'Linus Torvalds'),
(6, 'Ada Lovelace');

-- --------------------------------------------------------

--
-- Table structure for table `Journal`
--

CREATE TABLE `Journal` (
  `JournalID` int(11) NOT NULL,
  `DocID` int(11) NOT NULL,
  `Volume` smallint(6) NOT NULL,
  `EditorID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Journal`
--

INSERT INTO `Journal` (`JournalID`, `DocID`, `Volume`, `EditorID`) VALUES
(1, 24, 1, 1),
(2, 25, 2, 2),
(3, 26, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `Librarian`
--

CREATE TABLE `Librarian` (
  `UserID` int(11) NOT NULL,
  `BranchID` int(11) NOT NULL,
  `StartDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Librarian`
--

INSERT INTO `Librarian` (`UserID`, `BranchID`, `StartDate`) VALUES
(1, 1, '2020-01-15'),
(2, 2, '2019-06-01'),
(3, 3, '2021-03-20');

-- --------------------------------------------------------

--
-- Table structure for table `Lib_Branch`
--

CREATE TABLE `Lib_Branch` (
  `BranchID` int(1) NOT NULL,
  `Address` text NOT NULL,
  `PhoneNumber` char(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Lib_Branch`
--

INSERT INTO `Lib_Branch` (`BranchID`, `Address`, `PhoneNumber`) VALUES
(1, '100 Library Lane, Springfield, IL 62701', '2175551234'),
(2, '200 Book Boulevard, Springfield, IL 62702', '2175555678'),
(3, '300 Reading Road, Springfield, IL 62703', '2175559999');

-- --------------------------------------------------------

--
-- Table structure for table `Member`
--

CREATE TABLE `Member` (
  `UserID` int(11) NOT NULL,
  `Fines` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Member`
--

INSERT INTO `Member` (`UserID`, `Fines`) VALUES
(1, 0.00),
(2, 0.00),
(3, 2.50),
(4, 0.00),
(5, 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `UserID` int(11) NOT NULL,
  `Address` text NOT NULL,
  `Password` varchar(30) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `firstname` varchar(30) DEFAULT NULL,
  `lastname` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`UserID`, `Address`, `Password`, `is_admin`, `firstname`, `lastname`) VALUES
(1, '517 N 94th St, Milwaukee, WI 53226', 'admin', 1, 'Carson', 'Pagel'),
(2, '456 Elm St, Springfield, IL 62702', 'member123', 0, 'Bob', 'Reader'),
(3, '789 Oak Ave, Springfield, IL 62703', 'member456', 0, 'Carol', 'Patron'),
(4, '321 Pine Rd, Springfield, IL 62704', 'member789', 0, 'David', 'Borrower'),
(5, '654 Maple Dr, Springfield, IL 62705', 'member000', 0, 'Emma', 'Scholar');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Author`
--
ALTER TABLE `Author`
  ADD PRIMARY KEY (`AuthorID`);

--
-- Indexes for table `Book`
--
ALTER TABLE `Book`
  ADD PRIMARY KEY (`ISBN`),
  ADD UNIQUE KEY `DocID` (`DocID`),
  ADD KEY `AuthorID` (`AuthorID`);

--
-- Indexes for table `Borrows`
--
ALTER TABLE `Borrows`
  ADD PRIMARY KEY (`DocID`,`CopyNum`,`UserID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `Copy`
--
ALTER TABLE `Copy`
  ADD PRIMARY KEY (`DocID`,`CopyNum`),
  ADD KEY `BranchID` (`BranchID`),
  ADD KEY `copy_ibfk_3` (`UserID`);

--
-- Indexes for table `Director`
--
ALTER TABLE `Director`
  ADD PRIMARY KEY (`DirectorID`);

--
-- Indexes for table `Document`
--
ALTER TABLE `Document`
  ADD PRIMARY KEY (`DocID`);

--
-- Indexes for table `DVD`
--
ALTER TABLE `DVD`
  ADD PRIMARY KEY (`DVDID`),
  ADD KEY `DirectorID` (`DirectorID`),
  ADD KEY `DocID` (`DocID`);

--
-- Indexes for table `Editor`
--
ALTER TABLE `Editor`
  ADD PRIMARY KEY (`EditorID`);

--
-- Indexes for table `Journal`
--
ALTER TABLE `Journal`
  ADD PRIMARY KEY (`JournalID`),
  ADD KEY `EditorID` (`EditorID`),
  ADD KEY `DocID` (`DocID`);

--
-- Indexes for table `Librarian`
--
ALTER TABLE `Librarian`
  ADD PRIMARY KEY (`UserID`),
  ADD KEY `BranchID` (`BranchID`);

--
-- Indexes for table `Lib_Branch`
--
ALTER TABLE `Lib_Branch`
  ADD PRIMARY KEY (`BranchID`);

--
-- Indexes for table `Member`
--
ALTER TABLE `Member`
  ADD PRIMARY KEY (`UserID`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`UserID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Book`
--
ALTER TABLE `Book`
  ADD CONSTRAINT `book_ibfk_1` FOREIGN KEY (`AuthorID`) REFERENCES `Author` (`AuthorID`),
  ADD CONSTRAINT `book_ibfk_2` FOREIGN KEY (`DocID`) REFERENCES `Document` (`DocID`);

--
-- Constraints for table `Borrows`
--
ALTER TABLE `Borrows`
  ADD CONSTRAINT `borrows_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Member` (`UserID`),
  ADD CONSTRAINT `borrows_ibfk_2` FOREIGN KEY (`DocID`,`CopyNum`) REFERENCES `Copy` (`DocID`, `CopyNum`);

--
-- Constraints for table `Copy`
--
ALTER TABLE `Copy`
  ADD CONSTRAINT `copy_ibfk_1` FOREIGN KEY (`DocID`) REFERENCES `Document` (`DocID`),
  ADD CONSTRAINT `copy_ibfk_2` FOREIGN KEY (`BranchID`) REFERENCES `Lib_Branch` (`BranchID`),
  ADD CONSTRAINT `copy_ibfk_3` FOREIGN KEY (`UserID`) REFERENCES `Member` (`UserID`);

--
-- Constraints for table `DVD`
--
ALTER TABLE `DVD`
  ADD CONSTRAINT `dvd_ibfk_1` FOREIGN KEY (`DirectorID`) REFERENCES `Director` (`DirectorID`),
  ADD CONSTRAINT `dvd_ibfk_2` FOREIGN KEY (`DocID`) REFERENCES `Document` (`DocID`);

--
-- Constraints for table `Journal`
--
ALTER TABLE `Journal`
  ADD CONSTRAINT `journal_ibfk_1` FOREIGN KEY (`EditorID`) REFERENCES `Editor` (`EditorID`),
  ADD CONSTRAINT `journal_ibfk_2` FOREIGN KEY (`DocID`) REFERENCES `Document` (`DocID`);

--
-- Constraints for table `Librarian`
--
ALTER TABLE `Librarian`
  ADD CONSTRAINT `librarian_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`),
  ADD CONSTRAINT `librarian_ibfk_2` FOREIGN KEY (`BranchID`) REFERENCES `Lib_Branch` (`BranchID`);

--
-- Constraints for table `Member`
--
ALTER TABLE `Member`
  ADD CONSTRAINT `member_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
