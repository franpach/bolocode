#!/bin/bash

#SCRIPT PARA LA GESTIÓN DE BATALLAS DE ROBOCODE#
#Autores: Francisco Javier Pacheco Herranz y Javier Romero Pérez
#---------------------------PRIMERA PARTE: RECOLECCIÓN DE DATOS----------------------------#

#Funciones para modificar diferentes parámetros
modifica_grupo(){
	grupoOld=$grupo
	if ! [[ -z "$grupo" ]] # Indicamos el grupo actual de trabajo. Si no lo hay es que es el comienzo del script
	then
		echo "Actualmente el grupo activo es el grupo $grupo" 
	fi
	echo "¿A qué grupo perteneces?"
	echo "Escribe cancel para cancelar."
	read grupo
	GRUPO_CORRECTO=false
	while [ "$GRUPO_CORRECTO" = false ]; do
		if [[ "$grupo" =~ ^[0-9]+$ ]]
		then
			existe=$(php /var/www/bolotweet/scripts/getGroupId.php -G$grupo)
			if [ "$existe" = "Grupo no existente." ]
			then
				echo "Grupo no existente. Por favor, introduce un grupo existente";
				read grupo
			else
				GRUPO_CORRECTO=true	
			fi
		elif [ "$grupo" = "cancel" ]
		then
			grupo=$grupoOld
			if [ -z "$grupo" ]	# si es nada más entrar tiene que obligatoriamente decir un grupo
			then
				echo "Por favor, introduce un grupo con el que empezar a jugar: "
				read grupo
				
			else
				GRUPO_CORRECTO=true
				echo "Entendido, nos quedamos en el grupo $grupo"
			fi
		elif [ -z "$grupo" ]
		then
			echo "Por favor, introduce un grupo con el que empezar a jugar"
			read grupo
		else 
			existe=$(php /var/www/bolotweet/scripts/getGroupId.php -g$grupo)
			if [ "$existe" = "Grupo no existente." ]
			then
				echo "Grupo no existente. Por favor, introduce un grupo existente";
				read grupo
			else
				grupo=$existe # guardamos en grupo el ID del grupo que ha devuelto la llamada al php
				GRUPO_CORRECTO=true	
			fi
		fi
	done
	nombreFichero="battle_"$grupo
}

modifica_dias(){
	numDiasOld=$numDias
	echo "Actualmente el periodo de notas es de $numDias días"
	echo "¿Cuántos días de antigüedad quieres que tenga la media de notas?"
	echo "Escribe cancel para cancelar."
	read numDias

	while ! [[ "$numDias" =~ ^[0-9]+$ ]]; do
		if [ "$numDias" = "cancel" ] 
		then
			numDias=$numDiasOld
			echo "Entendido, los días de período actuales se mantienen en $numDias días"
		else
			echo "Por favor, introduce un valor correcto para el número de días: "
			read numDias
		fi
	done
}

modifica_tablero(){
	anchoOld=$ancho
	altoOld=$alto
	echo "Actualmente el tamaño del tablero es de $ancho x $alto"
	echo "Indica el tamaño del tablero"
	echo "Ancho:"
	echo "Escribe cancel para cancelar."
	read ancho
	ANCHO_CORRECTO=false
	while [ "$ANCHO_CORRECTO" = false ]; do
		if [[ "$ancho" =~ ^[0-9]+$ ]]
		then
			if [ $ancho -lt 400 ] || [ $ancho -gt 5000 ]
			then
				echo "La anchura del tablero ha de estar comprendida entre 400 y 5000"
				read ancho	
			else
				ANCHO_CORRECTO=true
			fi		
		else
			if [ "$ancho" = "cancel" ]
			then	
				ANCHO_CORRECTO=true
				ancho=$anchoOld
				echo "Entendido, el alto del tablero se mantiene en $ancho"
			else
				echo "Por favor, introduce un valor correcto para el ancho del tablero: "
				read ancho
			fi
		fi
	done
	echo "Alto:"
	echo "Escribe cancel para cancelar."
	read alto
	ALTO_CORRECTO=false
	while [ "$ALTO_CORRECTO" = false ]; do
		if [[ "$alto" =~ ^[0-9]+$ ]]
		then
			if [ $alto -lt 400 ] || [ $alto -gt 5000 ]
			then
				echo "La altura del tablero ha de estar comprendida entre 400 y 5000"
				read alto	
			else
				ALTO_CORRECTO=true
			fi		
		else
			if [ "$alto" = "cancel" ]
			then	
				ALTO_CORRECTO=true
				alto=$altoOld
				echo "Entendido, el alto del tablero se mantiene en $alto"
			else
				echo "Por favor, introduce un valor correcto para el alto del tablero: "
				read alto
			fi
		fi
	done
}

modifica_rondas(){
	numRondasOld=$numRondas
	echo "Actualmente el número de rondas es de $numRondas"
	echo "¿Cuántas rondas quieres batallar?"
	echo "Escribe cancel para cancelar."
	read numRondas	
	while ! [[ "$numRondas" =~ ^[0-9]+$ ]]; do
		if [ "$numRondas" = "cancel" ]
		then
			numRondas=$numRondasOld
			echo "Entendido, el número de rondas se mantiene en $numRondas"
		else
			echo "Por favor, introduce un valor correcto para el número de rondas: "
			read numRondas
		fi
	done
}

#Funciones para modificar/crear robots

modifica_nivel_robot() {
	# buscamos la media de los grades del usuario
	echo "no" > encontrado.txt
	cat $2 | while IFS=, read -r robot grade 
	do
		if [ "$robot" = $1 ]
		then
			echo "si" > encontrado.txt #solución sucia temporal
			menor=$(echo "$grade <= 1" | bc -l) # para tratar con floats
			if [ $menor -eq 1 ]
			then
				echo "El nivel del robot $1 es 1"
				cat Nivel1.txt > ./robots/$1.java # vuelca la plantilla a un fichero java
			else
				menor=$(echo "$grade <= 2" | bc -l)
				if [ $menor -eq 1 ]
				then
					echo "El nivel del robot $1 es 2"
					cat Nivel2.txt > ./robots/$1.java # vuelca la plantilla a un fichero java
				else
					echo "El nivel del robot $1 es 3"
					cat Nivel3.txt > ./robots/$1.java # vuelca la plantilla a un fichero java
				fi
			fi			
		fi
	done < $2
	ROBOT_ENCONTRADO=$(cat encontrado.txt)
	if [ $ROBOT_ENCONTRADO = "no" ] 
	then
		echo "¡Vaya! Parece que el usuario $1 no ha participado en Bolotweet. Se le asignará un robot de nivel 1"
		cat Nivel1.txt > ./robots/$1.java # vuelca la plantilla a un fichero java
	fi
	rm encontrado.txt

	sed -i 's/NOMBRECLASE/'$1'/g' ./robots/$1.java # cambia el nombre de la clase
	actualiza_color_codigo $1
}

actualiza_color_codigo() {
	if [ ! -f "robots/colores.csv" ]
	then
		echo "No se puede cargar el fichero de colores. Aplicando colores por defecto..."
		newColor="setColors(Color.white,Color.white,Color.black);"
		sed -i 's/setColors.*/'$newColor'/g' ./robots/$1.java			
	else
		esta=$(cat robots/colores.csv | grep $1)
		if [ ! -z "$esta" ]
		then
			cat robots/colores.csv | while IFS=, read -r robot cbody cgun cradar
			do
				if [ $robot = $1 ]
				then
					newColor="setColors(Color.$cbody,Color.$cgun,Color.$cradar);"
					sed -i 's/setColors.*/'$newColor'/g' ./robots/$1.java			
				fi
			done < "robots/colores.csv"
		else
			echo "No se ha encontrado una configuración de colores para este robot. Aplicando colores por defecto..."
			newColor="setColors(Color.white,Color.white,Color.black);"
			sed -i 's/setColors.*/'$newColor'/g' ./robots/$1.java			
			echo $1,"white,white,black" >> robots/colores.csv # ponemos al final el nuevo color
		fi
	fi	
}

compila_robot() {
	javac -classpath ../libs/robocode.jar ./robots/$1.java # compila
	jar -cvf ./robots/$1.jar ./robots/$1.java # crea el fichero .jar	
}

nuevo_robot() {
	# Comprobamos que el color es correcto y si no lo es ponemos los de por defecto
	listaColores="black blue green orange pink red white yellow"
	[[ $listaColores =~ (^|[[:space:]])"$3"($|[[:space:]]) ]] || 3="white"
	[[ $listaColores =~ (^|[[:space:]])"$4"($|[[:space:]]) ]] || 4="white"
	[[ $listaColores =~ (^|[[:space:]])"$5"($|[[:space:]]) ]] || 5="black"

	modificar_fichero_colores $1
	modifica_nivel_robot $1 $2
	actualiza_color_codigo $1 
}

comprueba_nombre_robot(){
				echo "Escribe cancel para cancelar."
				read robotName
				ROBOT_PERTENECE=false
				while [ "$ROBOT_PERTENECE" = false ]; do
					if [ "$robotName" = "cancel" ]
					then
						echo "Se ha cancelado la acción."
						CANCEL=true
						ROBOT_PERTENECE=true
					else
						robotPerteneceGrupo=$(php /var/www/bolotweet/scripts/checkrobocoderobot.php -G$grupo -n$robotName)
						if [ "$robotPerteneceGrupo" = "OK" ]
						then
							ROBOT_PERTENECE=true
						else
							echo "Ese robot no pertenece al grupo"
							echo "Introduce un usuario que pertenezca al grupo"
							read robotName
						fi
					fi
				done
}

modificar_colores_robot(){
	echo "¿Quieres elegir los colores de tu robot o usar los de por defecto? (S/N) (S: Elegir colores)"
	echo "Escribe cancel para cancelar."
	read elegirColores
	ELEGIR_COLORES_CORRECTO=false					
	while [ "$ELEGIR_COLORES_CORRECTO" = false ]; do
		if [ -z "$elegirColores" ]
		then
			echo "Por favor, introduce una respuesta"
			read elegirColores
		elif [ $elegirColores = "n" ] || [ $elegirColores = "N" ]
		then
			ELEGIR_COLORES_CORRECTO=true
			echo "Aplicando colores por defecto..."	
			colorBody="white"
			colorGun="white"
			colorRadar="black"
		elif [ $elegirColores = "s" ] || [ $elegirColores = "S" ]
		then
			ELEGIR_COLORES_CORRECTO=true
			echo "Los colores disponibles son: "
			echo "black blue green orange pink red white yellow"
			echo "Elige: colorBody colorGun colorRadar"
			read colorBody colorGun colorRadar
			COLORES_CORRECTO=false
			while [ "$COLORES_CORRECTO" = false ]; do
				if [ -z "$colorBody" ]
				then
					BODY_VACIO=true
																	
					echo "Selecciona un color para el cuerpo y el escáner:"
					read colorBody
				else
					BODY_VACIO=false
				fi
				if [ -z "$colorGun" ]										
				then
					GUN_VACIO=true
					echo "Selecciona un color para el cañón:"
					read colorGun
				else
					GUN_VACIO=false
				fi
				if [ -z "$colorRadar" ]										
				then
					RADAR_VACIO=true
					echo "Selecciona un color para el radar:"
					read colorRadar
				else
					RADAR_VACIO=false
				fi
				if [ "$BODY_VACIO" = false ] && [ "$GUN_VACIO" = false ] && [ "$RADAR_VACIO" = false ]
				then
					COLORES_CORRECTO=true
				fi
			done
		elif [ $elegirColores = "cancel" ] 
		then
			ELEGIR_COLORES_CORRECTO=true
			CANCEL=true
			echo "Se ha cancelado la acción."
		else
			echo "Introduce una respuesta correcta (S/N):"
			read elegirColores
		fi
	done
	if [ "$CANCEL" = false ]
	then 
		echo "¡Perfecto! Los colores del robot han sido modificados."
	fi
}

comprueba_existencia_robot(){
	while [ "$ROBOT_CREADO" = false ]; do	
		YA_CREADO=$(ls robots/ | grep $robotName.jar)
		if [ "$YA_CREADO" = $robotName.jar ] 
		then	
			SOBREESCRIBIR_CORRECTA=false
			echo "Ya existe un robot con ese nombre, ¿deseas sobreescribir? (S/N)"
			echo "Escribe cancel para cancelar."
			read sobreEscribir
			while [ "$SOBREESCRIBIR_CORRECTA" = false ]; do
				if [ $sobreEscribir = "n" ] || [ $sobreEscribir = "N" ]
				then
					sobreEscribir=false
					SOBREESCRIBIR_CORRECTA=true
					echo "Introduce otro nombre"
					comprueba_nombre_robot
				elif [ $sobreEscribir = "s" ] || [ $sobreEscribir = "S" ]
				then
					sobreEscribir=true
					SOBREESCRIBIR_CORRECTA=true
					ROBOT_CREADO=true 
					modificar_colores_robot
				elif [ $sobreEscribir = "cancel" ]
				then
					SOBREESCRIBIR_CORRECTA=true
					CANCEL=true
					ROBOT_CREADO=true 
					echo "Se ha cancelado la acción."
				else
					echo "introduce una respuesta correcta (S/N):"
					read sobreEscribir
				fi
			done
		else
			ROBOT_CREADO=true
			echo "¡Perfecto! El robot ha sido incluido para pelear."
		fi
	done	
}

modificar_fichero_colores(){
	# Comprobamos que el color es correcto y si no lo es ponemos los de por defecto
	listaColores="black blue green orange pink red white yellow"	
	[[ $listaColores =~ (^|[[:space:]])$colorBody($|[[:space:]]) ]] || colorBody="white"
	[[ $listaColores =~ (^|[[:space:]])$colorGun($|[[:space:]]) ]] || colorGun="white"
	[[ $listaColores =~ (^|[[:space:]])$colorRadar($|[[:space:]]) ]] || colorRadar="black"
	
	if [ ! -f "robots/colores.csv" ]
	then
		echo "Lo sentimos. No es posible en estos momentos guardar la configuración de los colores"
	else
		esta=$(cat robots/colores.csv | grep $1) # comprobamos que el robot está en la lista
		if [ ! -z "$esta" ]
		then # en caso de estar, se actualiza su fila con sus colores
			sed -i "s/$1,.*/$robotName,$colorBody,$colorGun,$colorRadar/g" robots/colores.csv
		else # en caso de no estar, ponemos al final el nuevo color
			echo $1,$colorBody,$colorGun,$colorRadar >> robots/colores.csv 
		fi
	fi
}

guardar_archivo_config() {
	if [ ! -f roboconfig.cfg ] # Si no hay archivo de configuración se crea uno con parámetros por defecto
	then
		echo "# ARCHIVO DE CONFIGURACION PARA ROBOPLUGIN

alto=600
ancho=800
numDias=7
numRondas=5" > roboconfig.cfg
	fi

	echo "¿Quieres guardar esta nueva configuración? (S/N)"
	read guardarConfig
	GUARDAR_CONFIG_CORRECTO=false
	while [ "$GUARDAR_CONFIG_CORRECTO" = false ]; do
		if [ "$guardarConfig" = "s" ] || [ "$guardarConfig" = "S" ]
		then
			GUARDAR_CONFIG_CORRECTO=true
			sed -i 's/'$1'.*/'$1=$2'/g' ./roboconfig.cfg
		elif [ "$guardarConfig" = "n" ] || [ "$guardarConfig" = "N" ]
		then
			GUARDAR_CONFIG_CORRECTO=true
		else
			echo "Por favor, introduce una respuesta correcta (S/N)"
			read guardarConfig
		fi
	done
}

muestra_grades() {
	if [ ! -f "$1" ]
	then
		echo "No hay fichero de grades para este grupo"
	else
		cat $1 | while IFS=, read -r user media
		do
			echo $user $media
		done < $1
	fi
}

pwd=`pwd`
cd "${0%/*}"

echo 	"¡Hola! 
¡Bienvenido al generador de batallas Robocode!"

echo "Leyendo la configuración..." >&2
source ./roboconfig.cfg

modifica_grupo # Seleccionamos el grupo sobre el que trabajar
/var/www/bolotweet/scripts/updategradesfileandshow.sh $grupo $numDias 
ficheroGrades="/tmp/g"$grupo"_grades.csv"
nicks=$(php /var/www/bolotweet/scripts/listGroup.php -G$grupo | tr " " "\n")
echo "ACTUALIZANDO ROBOTS..."
muestra_grades $ficheroGrades
for nick in $nicks 
do
	modifica_nivel_robot $nick $ficheroGrades
	compila_robot $nick
done

salir=false
while [ "$salir" = false ]; do
	echo "-------------------------------------------------------------------------------"
	echo "Elige una de las siguientes opciones:"
	echo "1.-Modificar opciones de notas/grupo.		2.-Modificar/crear robots."
	echo "3.-Modificar opciones de batalla.		4.-Generar batalla."
	echo "0.-Salir."
	echo "-------------------------------------------------------------------------------"
	echo "Opción:"	

	read opcion

	while ! [[ "$opcion" =~ ^[0-4]+$ ]]; do
		echo "Por favor, introduce una opcion valida: "
		read opcion
	done

	case $opcion in
		0) echo "Saliendo..."
		salir=true
		;;
		1) salir1=false
		   while [ "$salir1" = false ]; do
			   echo "-------------------------------------------------------------------------------"
			   echo "Elige una de las siguientes acciones:"
			   echo "1.-Modificar grupo de trabajo.		2.-Modificar periodo de medias."
			   echo "0.-Volver atrás."
			   echo "-------------------------------------------------------------------------------"
			   echo "Opción:"
			   read opcion1
			   while ! [[ "$opcion1" =~ ^[0-2]+$ ]]; do
				   echo "Por favor, introduce una opcion valida: "
				   read opcion1
			   done
			   case $opcion1 in 
				   0) /var/www/bolotweet/scripts/updategradesfileandshow.sh $grupo $numDias 
				      ficheroGrades="/tmp/g"$grupo"_grades.csv"
				      salir1=true #Actualizamos los ficheros correspondientes antes de salir por si ha habido cambios
				   ;;
				   1) modifica_grupo
					 nicks=$(php /var/www/bolotweet/scripts/listGroup.php -G$grupo | tr " " "\n")
				      nombreFichero="battle_"$grupo
					 ficheroGrades="/tmp/g"$grupo"_grades.csv"
					 /var/www/bolotweet/scripts/updategradesfileandshow.sh $grupo $numDias 
					 echo "ACTUALIZANDO ROBOTS..."
					 for nick in $nicks 
				      do
						 modifica_nivel_robot $nick $ficheroGrades
						 compila_robot $nick
				      done
					 muestra_grades $ficheroGrades
				   ;;
				   2) modifica_dias
					 echo "Actualizando los niveles de los robots..."
					 /var/www/bolotweet/scripts/updategradesfileandshow.sh $grupo $numDias 
					 for nick in $nicks 
				      do
						 modifica_nivel_robot $nick $ficheroGrades
						 compila_robot $nick
				      done
					 guardar_archivo_config "numDias" $numDias
					 muestra_grades $ficheroGrades
				   ;;
				   esac
		   done

		;;
		2) salir2=false
		   while [ "$salir2" = false ]; do
			   echo "-------------------------------------------------------------------------------"
			   echo "Elige una de las siguientes acciones:"
			   echo "1.-Listar robots existentes.		2.-Crear nuevo robot."
			  
			   echo "3.-Modificar colores de un robot.	0.-Salir."
			   echo "-------------------------------------------------------------------------------"
			   echo "Opción:"
			   read opcion2
			   while ! [[ "$opcion2" =~ ^[0-3]+$ ]]; do
				   echo "Por favor, introduce una opcion valida: "
				   read opcion2
			   done
			   case $opcion2 in 
				   0) 
				      salir2=true 
				   ;;
				   1) echo "Estos son los robots disponibles:"
				      for nick in $nicks 
				      do
					 	ls robots/ | grep "$nick.jar"
				      done
				     
				   ;;
				   2) ROBOT_CREADO=false
				      CANCEL=false
				      echo "Introduce el nombre que le quieres dar al robot: "
				      comprueba_nombre_robot
				      if [ "$CANCEL" = false ]
				      then
					      comprueba_existencia_robot
					      if [ "$CANCEL" = false ]
				      	      then
							 modificar_colores_robot
							 modificar_fichero_colores $robotName
						      nuevo_robot $robotName $ficheroGrades $colorBody $colorGun $colorRadar
						      compila_robot $robotName
					      fi
				      fi
				   ;;
				   3) echo "Introduce el nombre del robot que quieres modificar"
   					 echo "Escribe cancel para cancelar."
					 read robotName
				      ROBOT_CORRECTO=false
				      while [ "$ROBOT_CORRECTO" = false ]; do
						 if [ -z "$robotName" ]								
						 then
						 	echo "Por favor, introduce un nombre"
							read robotName
					      elif [[ $nicks = *"$robotName"* ]]
					      then
							ROBOT_CORRECTO=true
					      	modificar_colores_robot
							modificar_fichero_colores $robotName
							actualiza_color_codigo $robotName
							compila_robot $robotName
					      else
							if [ "$robotName" == "cancel" ]
							then
								ROBOT_CORRECTO=true
							else
								echo "Ese robot no se ha cargado todavía"
								echo "Escoge otro robot"
								echo "Escribe cancel para cancelar."
								read robotName
							fi
					      fi
				      done
				   ;;
				  
				   esac
		   done
			


		;;
		3) salir3=false
		   while [ "$salir3" = false ]; do
			   echo "-------------------------------------------------------------------------------"
			   echo "Elige una de las siguientes acciones:"
			   echo "1.-Modificar tamaño de tablero.		2.-Modificar número de rondas."
			   echo "0.-Salir."
			   echo "-------------------------------------------------------------------------------"
			   echo "Opción:" 
			   read opcion3
			   while ! [[ "$opcion3" =~ ^[0-2]+$ ]]; do
				   echo "Por favor, introduce una opcion valida: "
				   read opcion3
			   done
			   case $opcion3 in 
				   0) salir3=true 
				   ;;
				   1) modifica_tablero
					 guardar_archivo_config "ancho" $ancho
					 guardar_archivo_config "alto" $alto
				   ;;
				   2) modifica_rondas
					 guardar_archivo_config "numRondas" $numRondas
				   ;;
				   esac
		   done

		;;
		4) 
		   /var/www/bolotweet/scripts/generaBatalla.sh $numRondas $ancho $alto $nombreFichero "${nicks[@]}"
		   
		;;
	esac
done

