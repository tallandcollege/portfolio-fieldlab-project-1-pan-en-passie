CREATE DATABASE IF NOT EXISTS pan_en_passie;
USE pan_en_passie;

-- Role first, because other tables reference it
CREATE TABLE IF NOT EXISTS Role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Description VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(255) NOT NULL,
    LastName VARCHAR(255) NOT NULL,
    Username VARCHAR(255) NOT NULL UNIQUE, 
    Email VARCHAR(255) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    RoleID INT,
    FOREIGN KEY (RoleID) REFERENCES Role(id)
);

CREATE TABLE IF NOT EXISTS Class (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ClassName VARCHAR(255) NOT NULL,
    Description TEXT,
    MaxStudents Varchar(3),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CreateRecipePerms BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS Student (
    `User.id` INT,
    `Class.id` INT,
    `Role.id` INT,

    FOREIGN KEY (`User.id`) REFERENCES users(id),
    FOREIGN KEY (`Class.id`) REFERENCES Class(id),
    FOREIGN KEY (`Role.id`) REFERENCES Role(id),

    PRIMARY KEY (`User.id`, `Class.id`)
);

CREATE TABLE IF NOT EXISTS Recipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `User.id` INT,
    `Class.id` INT NULL,
    Name VARCHAR(255) NOT NULL,
    Description TEXT,
    Instructions TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`User.id`) REFERENCES users(id),
    FOREIGN KEY (`Class.id`) REFERENCES Class(id)
);

CREATE TABLE IF NOT EXISTS Ingredient (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Category VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS RecipeIngredient (
    `Recipe.id` INT,
    `Ingredient.id` INT,
    Pieces VARCHAR(255) NULL,
    Grams VARCHAR(255) NULL,
    Milliliters VARCHAR(255) NULL,
    IngredientRole Varchar(255) NULL,

    FOREIGN KEY (`Recipe.id`) REFERENCES Recipe(id),
    FOREIGN KEY (`Ingredient.id`) REFERENCES Ingredient(id),

    PRIMARY KEY (`Recipe.id`, `Ingredient.id`)
);
