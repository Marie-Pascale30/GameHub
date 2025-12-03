DROP DATABASE IF EXISTS GAMEHUB;

CREATE DATABASE GAMEHUB;

USE GAMEHUB;

DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS favorites;

CREATE TABLE users (
id INT auto_increment PRIMARY KEY,
username VARCHAR(40) UNIQUE NOT NULL,
email VARCHAR(120) UNIQUE NOT NULL,
password_hash VARCHAR(255) NOT NULL,
date_inspription DATETIME DEFAULT now()
);

CREATE TABLE games (
id INT auto_increment PRIMARY KEY,
title VARCHAR(120) NOT NULL,
platform VARCHAR(60) NOT NULL,
cover_url VARCHAR(255) NOT NULL 
);

CREATE TABLE favorites (
user_id INT NOT NULL,
game_id INT NOT NULL,
date_ajout DATETIME DEFAULT now(),
PRIMARY KEY (user_id, game_id),
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

INSERT INTO games (title, platform, cover_url)
values ('Legend of Zelda','Nintendo 64, GameCube, Wii','https://www.bing.com/images/search?view=detailV2&ccid=rY6VdMhU&id=05B446A17F70431A53BA3C5FBA26203D6B4D1EEF&thid=OIP.rY6VdMhUBJhPBRlppFP9hgFNC7&mediaurl=https%3a%2f%2fget.wallhere.com%2fphoto%2fThe-Legend-of-Zelda-The-Legend-of-Zelda-Twilight-Princess-Wolf-Link-1356903.jpg&cdnurl=https%3a%2f%2fth.bing.com%2fth%2fid%2fR.ad8e9574c85404984f051969a453fd86%3frik%3d7x5Naz0gJrpfPA%26pid%3dImgRaw%26r%3d0&exph=1080&expw=1920&q=legende+de+zelda&FORM=IRPRST&ck=F9D76BAB90B6C868A98889AF645546AA&selectedIndex=28&itb=0&ajaxhist=0&ajaxserp=0'),
('Chrono Tigger','Nintendo, Wii','https://www.bing.com/images/search?view=detailV2&ccid=P6kMS9vm&id=AB4A58F510FFCC1CCE375546F21F361344CD66B4&thid=OIP.P6kMS9vmTsjDqM5qAri9AgHaEK&mediaurl=https%3a%2f%2fexputer.com%2fwp-content%2fuploads%2f2022%2f12%2fChrono-Trigger.jpg&cdnurl=https%3a%2f%2fth.bing.com%2fth%2fid%2fR.3fa90c4bdbe64ec8c3a8ce6a02b8bd02%3frik%3dtGbNRBM2H%252fJGVQ%26pid%3dImgRaw%26r%3d0&exph=720&expw=1280&q=chrono+trigger+&FORM=IRPRST&ck=87B647DC44FCD44C538C1B4930B767D7&selectedIndex=0&itb=0&ajaxhist=0&ajaxserp=0'),
('Final Fantasy', 'playstation, Switch, PC, PSP, Xbox One','https://www.bing.com/images/search?view=detailV2&ccid=vCMFppED&id=34211EF32D7B16FC6590656BCE3D0216933B8DAE&thid=OIP.vCMFppEDPGr0MLWBAczyeQHaKn&mediaurl=https%3a%2f%2fpics.filmaffinity.com%2ffinal_fantasy_vii-870240723-large.jpg&cdnurl=https%3a%2f%2fth.bing.com%2fth%2fid%2fR.bc2305a691033c6af430b58101ccf279%3frik%3dro07kxYCPc5rZQ%26pid%3dImgRaw%26r%3d0&exph=1200&expw=837&q=final+fantasy+vii&FORM=IRPRST&ck=150CAF97928D0413666983BBE485E249&selectedIndex=3&itb=0&ajaxhist=0&ajaxserp=0'),
('Metal Gear Solid', 'Playstation 2','https://www.bing.com/images/search?view=detailV2&ccid=7Uro%2fPMh&id=00FC60E519B1F55C50047AEB0DC45C2982482875&thid=OIP.7Uro_PMhprqGh0RHCAQgZgHaEo&mediaurl=https%3a%2f%2f4kwallpapers.com%2fimages%2fwallpapers%2fmetal-gear-solid-1680x1050-11503.jpeg&cdnurl=https%3a%2f%2fth.bing.com%2fth%2fid%2fR.ed4ae8fcf321a6ba8687444708042066%3frik%3ddShIgilcxA3reg%26pid%3dImgRaw%26r%3d0&exph=1800&expw=2880&q=metal+gear+solid&FORM=IRPRST&ck=663ECC7DCBC12D2E55226694E02AE68D&selectedIndex=2&itb=0&ajaxhist=0&ajaxserp=0'),
('Super Mario Bros','WiiU, playstation, Switch, Xbox One','https://www.bing.com/images/search?view=detailV2&ccid=7Uro%2fPMh&id=00FC60E519B1F55C50047AEB0DC45C2982482875&thid=OIP.7Uro_PMhprqGh0RHCAQgZgHaEo&mediaurl=https%3a%2f%2f4kwallpapers.com%2fimages%2fwallpapers%2fmetal-gear-solid-1680x1050-11503.jpeg&cdnurl=https%3a%2f%2fth.bing.com%2fth%2fid%2fR.ed4ae8fcf321a6ba8687444708042066%3frik%3ddShIgilcxA3reg%26pid%3dImgRaw%26r%3d0&exph=1800&expw=2880&q=metal+gear+solid&FORM=IRPRST&ck=663ECC7DCBC12D2E55226694E02AE68D&selectedIndex=2&itb=0&ajaxhist=0&ajaxserp=0')
;