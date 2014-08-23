4pics1word-helper
=================

A helper to find solutions for the games "4 Pics 1 Word", "Icomania", "Pic Combo", "Guess the shadow" or "Mega Quiz"

Some required files are not made by me and you will need to get them other places and copy them to the paths indicated (create the folders):
arial.ttf: Get it from c:\windows\fonts on a windows computer

The data from the games should be put in a folder named "data".
Inside that create a folder for each supported game, "4pics1word" and "icomania".
Each folder should contain a data file and a folder named "images" containing all the images from the game.
The data file for 4 Pics 1 Word is named "photodata.txt", and the file Icomania is named "icomania_en.json".
The pictures for both games has names starting with a undscore followed by a number.
For 4 Pics 1 Word the pictures are in .jpg format, but for Icomania they are in .png.

All the files are located in the respective .ipa files, in the "Payload/[App name].app" folder.

The script can be run on the command line or in a web browser.
The index.php file is the browser version.
Run it on the command line like this: php 4pics1word.php [available letters] [number of letters in solution word] [4pics1word|icomania]

If you like it, please star it on github.
You are welcome to fork it and make improvements.