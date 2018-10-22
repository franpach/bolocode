#!/bin/bash
#
#   @authors   	Francisco Javier Pacheco Herranz <franpach@ucm.es>
#		Javier Romero Pérez <javrom01@ucm.es>	
#
# Este script está hecho para actualizar todos los ficheros con la nota media de los usuarios de un grupo
# 
# El script se ejecuta de la siguiente forma:
# 	./updategradesfileandshow group ndays
#
# Siendo group el ID o el nickname del grupo a actualizar
# Siendo ndays el número de días de antigüedad de tweets que se quieren medir

pwd=`pwd`
cd "${0%/*}"

if [ -z "$1" ] # si no se han introducido los parámetros
then
	echo "Tienes que indicar qué grupo quieres actualizar"
	echo "Usage: updategradesfileandshow.sh groupid numdays"
	exit 1
fi

if [ -z "$2" ] # si no se han introducido los parámetros
then
	echo "Tienes que indicar qué número de días quieres tener en cuenta"
	echo "Usage: updategradesfileandshow.sh groupid numdays"
	exit 1
fi

#vActualizamos el fichero
# Si se ha introducido un número (ID) se llama al script con la opción -G y si son letras(nickname) con la opción -g
if [[ "$1" =~ ^[0-9]+$ ]]
then
	php updategradesfile.php -G$1 -d$2
else
	php updategradesfile.php -g$1 -d$2
fi




