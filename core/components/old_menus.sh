#!/bin/bash

if [ -d com_menus_old ]; then
	mv com_menus com_menus_new
	mv com_menus_old com_menus
fi
