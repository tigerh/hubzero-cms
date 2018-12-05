#!/bin/bash

if [ -d com_menus_new ]; then
	mv com_menus com_menus_old
	mv com_menus_new com_menus
fi
