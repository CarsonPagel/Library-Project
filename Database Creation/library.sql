-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 21, 2025 at 01:33 AM
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

-- --------------------------------------------------------

--
-- Table structure for table `Book`
--

CREATE TABLE `Book` (
  `ISBN` int(11) NOT NULL,
  `DocID` int(11) NOT NULL,
  `AuthorID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `UserID` int(11) NOT NULL,
  `Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Director`
--

CREATE TABLE `Director` (
  `DirectorID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Document`
--

CREATE TABLE `Document` (
  `DocID` int(11) NOT NULL,
  `Title` varchar(1000) NOT NULL,
  `Publisher` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `DVD`
--

CREATE TABLE `DVD` (
  `DVDID` int(11) NOT NULL,
  `DocID` int(11) NOT NULL,
  `DirectorID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Editor`
--

CREATE TABLE `Editor` (
  `EditorID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `Librarian`
--

CREATE TABLE `Librarian` (
  `UserID` int(11) NOT NULL,
  `BranchID` int(11) NOT NULL,
  `StartDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Lib_Branch`
--

CREATE TABLE `Lib_Branch` (
  `BranchID` int(11) NOT NULL,
  `Address` text NOT NULL,
  `PhoneNumber` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Member`
--

CREATE TABLE `Member` (
  `UserID` int(11) NOT NULL,
  `Fines` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `UserID` int(11) NOT NULL,
  `Address` text NOT NULL,
  `Password` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
