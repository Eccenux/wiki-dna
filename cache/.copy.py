#! /usr/bin/python
###############################################
# @package FotkiMove
# @brief Copy cache to folders
# 
# @author
# 	- Maciej "Nux" Jaros
# 	- License: CC-BY-SA http://creativecommons.org/licenses/by-sa/3.0/
#
# @version
#	0.0.1
###############################################


import re
import shutil
import os, sys
from datetime import date

#
# Settings
#
"""
# check and set args
if len(sys.argv)<2:
	exit()
strSDCardDir = sys.argv[1]
strFotkiDir = sys.argv[2]
"""
"""
# testing...
strSDCardDir = r'e:\DCIM\119_PANA'
strFotkiDir = r'c:\Users\Nux\Pictures\Fotki\2011'
#"""
strDir = r'.'

##
# @brief Arch file
#
# @param strDir Source/dest base dir
# @param strFName Source file name
#
def archFile(strDir, strFName):
	# get dest. dir. name from file name
	strDirName = re.sub (r'^.+_==_(.+)\.php$', r'\1', strFName)
	if strDirName == strFName:
		return 0
	strDirName = re.sub (r'-', r'/', strDirName)
	# setup paths
	strSrcPath = os.path.join(strDir, strFName)
	strDstDir = os.path.join(strDir, strDirName)
	strDstPath = os.path.join(strDstDir, strFName)
	# create dst dir
	if os.path.exists(strDstPath):
		print 'NOT copied '+strSrcPath+' '+strDstPath+' [destination exists!]'
		return 0
	if not os.path.isdir(strDstDir):
		os.makedirs(strDstDir)
	# copy file
	shutil.copy2(strSrcPath, strDstPath)
	print 'copied '+strSrcPath+' to '+strDstPath
	return 1
	
#
# Copy each file one by one from strDir
#
for f in os.listdir(strDir):
	if os.path.isfile(os.path.join(strDir, f)):
		archFile(strDir, f)
		#break
