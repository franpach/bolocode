import java.io.File;
import java.io.FileWriter;
import java.io.IOException;

import org.apache.bcel.classfile.Field;

import robocode.control.*;
import robocode.control.events.*;
 
 //
 // Lanzamiento de una batalla en Robocode gracias al paquete RobocodeEngine
 // @author Francisco Javier Pacheco Herranz 
 // @author Javier Romero Pérez
 // Adaptación del código de Flemming N. Larsen
 //
 public class BattleRunner {

	public static void main(String[] args) {
    	
         RobocodeEngine.setLogMessagesEnabled(false);

         RobocodeEngine engine = new RobocodeEngine(new java.io.File("../")); // Run from current working directory

         engine.addBattleListener(new BattleObserver());
 
         engine.setVisible(true);
      
         // Setup the battle specification
         String robots=args[3];
         int numberOfRounds = Integer.parseInt(args[0]); 
         BattlefieldSpecification battlefield = new BattlefieldSpecification(Integer.parseInt(args[1]), Integer.parseInt(args[2])); // 800x600
         RobotSpecification[] selectedRobots = engine.getLocalRepository(robots); 
         BattleSpecification battleSpec = new BattleSpecification(numberOfRounds, battlefield, selectedRobots);

         engine.runBattle(battleSpec, true); // Running the battle 
 
         engine.close();
 
         System.exit(0);
     }
 }
 
 class BattleObserver extends BattleAdaptor {
 
     private Object interfaz;

     public void onBattleCompleted(BattleCompletedEvent e) {
    	 System.out.println("-- Battle has completed --");
    	 // Printing battle results
         System.out.println("Battle results:");
    	 try {
    		 File file = new File("/tmp/tmpresult.csv");
    		 FileWriter fileWriter = new FileWriter(file);
    		 for (robocode.BattleResults result : e.getSortedResults()) {
                 System.out.println("  " + result.getTeamLeaderName() + ": " + result.getScore() + " " + result.getFirsts());
                 fileWriter.write(result.getTeamLeaderName()+","+result.getScore()+","+result.getFirsts() + "\n");
                 fileWriter.flush();
             }
    		 fileWriter.close();
    	 }
    	 catch (IOException ioe) {
    		  
    	 }                      
     }

     // Event handling
     public void onBattleMessage(BattleMessageEvent e) {
         System.out.println("Msg> " + e.getMessage());
     }
 
     public void onBattleError(BattleErrorEvent e) {
         System.out.println("Err> " + e.getError());
     }
 }
 
