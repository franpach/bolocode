#!/bin/bash
#
#   @authors   	Francisco Javier Pacheco Herranz <franpach@ucm.es>
#		Javier Romero Pérez <javrom01@ucm.es>	
#
# Este script está hecho para actualizar todos los ficheros que registran los resultados de las batallas de robocode.
# 
#

pwd=`pwd`
cd "${0%/*}"

if [ ! -d "/tmp/batallas/" ]
then
	echo "No hay resultados por actualizar"
else
	# almacenados las batallas por registrar
	battles=$(ls /tmp/batallas/ | grep "battle_")

	if [ -z "$battles" ] # si no hay resultados es que no hay nada por actualizar
	then
		echo "No hay resultados por actualizar"
	else
		# hacemos un split quitando los espacios generados por el ls entre los nombres de cada fichero
		battles=$(echo $battles | tr " " "\n")

		# para cada fichero resultante, lo subimos a la base de datos
		for b in $battles
		do
			b="/tmp/batallas/"$b # concatenamos el /tmp/ para que bolotweet lo lea correctamente
			grupo=${b:21:1} # nos quedamos con el char de la posición 7 que es el número de grupo
			php updaterobocodescores.php -f$b -G$grupo
			rm $b # borramos el fichero para que en la próxima actualización no repitan resultados
		done
	fi
fi

