#!/bin/bash
pwd=`pwd`
cd "${0%/*}"

echo "Bienvenido al script generador de robots"
cat $1 | while IFS=, read -r robot cbody cgun cradar
	do
		# primero se comprueba que el robot pertenece al grupo	
		# si no existe se avisa por mensaje pero se siguen cargando el resto de robots del fichero
		robotPerteneceGrupo=$(php /var/www/bolotweet/scripts/checkrobocoderobot.php -G$2 -n$robot)
		if [ "$robotPerteneceGrupo" = "OK" ]
		then
			# se comprueba si los colores son correctos
			listaColores="black blue green orange pink red white yellow"
			[[ $listaColores =~ (^|[[:space:]])"$cbody"($|[[:space:]]) ]] || cbody="white"
			[[ $listaColores =~ (^|[[:space:]])"$cgun"($|[[:space:]]) ]] || cgun="white"
			[[ $listaColores =~ (^|[[:space:]])"$cradar"($|[[:space:]]) ]] || cradar="black"

			# cuando se crea un grupo, no hay grades por lo que el nivel de los robots es 1	
			cat Nivel1.txt > ./robots/$robot.java 
			sed -i 's/NOMBRECLASE/'$robot'/g' ./robots/$robot.java # cambia el nombre de la clase
			newColor="setColors(Color.$cbody,Color.$cgun,Color.$cradar);"
			sed -i 's/setColors.*/'$newColor'/g' ./robots/$robot.java
		
			# compilamos el código del robot
			javac -classpath ../libs/robocode.jar ./robots/$robot.java # compila
			jar -cvf ./robots/$robot.jar ./robots/$robot.java # crea el fichero .jar	

			# guardamos el robot en colores.csv
			# en caso de aparecer, sobreescribe sus antiguos colores
			echo "no" > c.txt
			cat "robots/colores.csv" | while IFS=, read -r robot2 
			do
				if [ "$robot2" = $robot ]
				then
					echo "si" > c.txt
				fi
			done < "robots/colores.csv"
			si=$(cat c.txt)

			if [ "$si" = "si" ]
			then
				sed -i "s/$robot,.*/$robot,$cbody,$cgun,$cradar/g" robots/colores.csv
			else
				echo $robot,$cbody,$cgun,$cradar >> robots/colores.csv # ponemos al final el nuevo color
			fi
			rm c.txt						
		else
			echo "El robot $robot no pertenece a este grupo"
		fi		
	done < $1
