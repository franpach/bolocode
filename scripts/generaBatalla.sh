#!/bin/bash
#
#   @authors   	Francisco Javier Pacheco Herranz <franpach@ucm.es>
#				Javier Romero Pérez <javrom01@ucm.es>	
#
# Este script está hecho para actualizar todos los ficheros con la nota media de los usuarios de un grupo
# 
# El script se ejecuta de la siguiente forma:
# 	./generaBatalla rondas ancho alto fichero robots
#
# Siendo robots los robots disponibles para elegir
# Siendo rondas el número de rondas que se van a ejecutar en la batalla
# Siendo ancho el ancho del tablero
# Siendo alto el alto del tablero
# Siendo fichero el nombre que se le quiere dar al archivo que contendrà los resultados de la batalla

batalla_normal() {
	ROBOT_ESCOGIDOS=false
	while [ "$ROBOT_ESCOGIDOS" = false ]; do
		echo "Estos son los robots disponibles para tu grupo"
		for nick in ${array[@]} 
		do
			ls robots/ | grep "$nick.jar"
	
		done
		echo "Escoge un robot"
		read robotName
		ROBOT_CORRECTO=false
		while [ "$ROBOT_CORRECTO" = false ]; do
			if [ -z "$robotName" ]
			then
				echo "Por favor, introduce una respuesta"
				read robotName
			elif [[ ${array[@]} = *"$robotName"* ]]
			then
				ya_incluido=$(echo $lista_robots | grep "$robotName*") # no pueden repetirse robots dentro de una batalla
				if [ -z "$ya_incluido" ]
				then
					ROBOT_CORRECTO=true	
					num_robots=$(( num_robots + 1 ))
					lista_robots=$lista_robots,$robotName*
					nombreFichero=$nombreFichero-$robotName	
				else
					echo "No pueden repetirse robots dentro de una batalla"
					echo "Introduce un nombre correcto."
					read robotName
				fi
			else
				if [ $robotName = "cancel" ] 
				then	
					ROBOT_CORRECTO=true
				else
					echo "El nombre que has introducido no se corresponde con ninguno de los de la lista."
					echo "Introduce un nombre correcto."
					read robotName
				fi
			fi
		done
		OPCION_CORRECTA=false
		echo "Se ha escogido un robot correctamente."
		echo "¿Quieres escoger otro robot? (S/N)"
		read opcion
		while [ "$OPCION_CORRECTA" = false ]; do
			if [ -z "$opcion" ]	
			then
				echo "Por favor, introduce una respuesta"
				read opcion
			elif [ $opcion = "n" ] || [ $opcion = "N" ]
			then
				OPCION_CORRECTA=true
				if [ "$num_robots" -le "1" ]
				then
					echo "¡ATENCION!"
					echo "No se puede iniciar una batalla con menos de 2 robots"
				else
					ROBOT_ESCOGIDOS=true	
				fi
			elif [ $opcion = "s" ] || [ $opcion = "S" ]
			then
				OPCION_CORRECTA=true
			else
				echo "introduce una respuesta correcta (S/N):"
				read opcion
			fi
		done
	done

	lista_robots="${lista_robots:1}" # quitamos la primera coma generada en el string de robots
	
}

robo_rumble() {
	for nick in ${array[@]} 
	do
		esrobot=$(ls robots/ | grep "$nick.jar")
		if [ ! -z "$esrobot" ]
		then
			lista_robots=$lista_robots,$nick*
			nombreFichero=$nombreFichero-$nick	
		fi
	done
	lista_robots="${lista_robots:1}" # quitamos la primera coma generada en el string de robots
}

if [ -z "$1" ] # si no se han introducido los parámetros
then
	echo "Tienes que indicar cuántas rondas quieres utilizar"
	echo "Usage: generaBatalla.sh rondas ancho alto fichero robots"
	exit 1
fi

if [ -z "$2" ] # si no se han introducido los parámetros
then
	echo "Tienes que indicar el ancho del tablero"
	echo "Usage: generaBatalla.sh rondas ancho alto fichero robots"
	exit 1
fi
if [ -z "$3" ] # si no se han introducido los parámetros
then
	echo "Tienes que indicar el alto del tablero"
	echo "Usage: generaBatalla.sh rondas ancho alto fichero robots"
	exit 1
fi
if [ -z "$4" ] # si no se han introducido los parámetros
then
	echo "Tienes que indicar el nombre del fichero"
	echo "Usage: generaBatalla.sh rondas ancho alto fichero robots"
	exit 1
fi

if [ -z "$5" ] # si no se han introducido los parámetros
then
	echo "Tienes que indicar los robots que quieres utilizar"
	echo "Usage: generaBatalla.sh rondas ancho alto fichero robots"
	exit 1
fi


array=( "$@" )
num_robots=0
nombreFichero=$4
lista_robots=""
echo "¿Quieres escoger los robots a batallar o hacer un RoboRumble?"
echo "(E/R) E: escoger, R: RoboRumble"
read tipoBatalla
TIPOBATALLA_CORRECTO=false
while [ "$TIPOBATALLA_CORRECTO" = false ]; do
	if [ -z "$tipoBatalla" ]
	then
		echo "Por favor, introduce una respuesta"
		read tipoBatalla
	elif [ $tipoBatalla = "e" ] || [ $tipoBatalla = "E" ]
	then
		TIPOBATALLA_CORRECTO=true
		batalla_normal
	elif [ $tipoBatalla = "r" ] || [ $tipoBatalla = "R" ]
	then
		TIPOBATALLA_CORRECTO=true
		robo_rumble
	else
		echo "Introduce una respuesta correcta (E/R)"
		read tipoBatalla
	fi
done

echo "GENERANDO BATALLA"
echo "Espere un momento..."
java -jar BattleRunner.jar $1 $2 $3 $lista_robots
date=$(date +%Y%m%d)
hour=$(date +%H%M%S)
nombreFichero=$nombreFichero-$date-$hour

# Comprobamos si la batalla ha finalizado correctamente mirando si el fichero con el resultado se ha generado
if [ -f /tmp/tmpresult.csv ]
then
	directory=/tmp/batallas
	if [ ! -d "$directory" ]
	then
		mkdir /tmp/batallas
	fi
	mv /tmp/tmpresult.csv /tmp/batallas/$nombreFichero.csv
else
	echo "La batalla no ha podido finalizar su ejecución."
fi



