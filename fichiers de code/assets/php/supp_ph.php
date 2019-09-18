<?php
session_start();

$conn =new PDO('mysql:host=localhost;dbname=projet;charset=utf8', 'root', '');
$type_user="enseignant";
$user=$_SESSION['id'];
$date_m=date("Y-m-d H:i:s");

$nom_projet =$_SESSION['projet'];
$nom_phase=$_GET['phase'];


$action="a supprimé la phase ".$nom_phase." du projet ".$nom_projet;


$requete = $conn->prepare('SELECT * FROM projet WHERE nom= :nom_projet');
$requete->bindValue(':nom_projet',$nom_projet) ;
$requete->execute();
$row_pr = $requete->fetch();

$requete = $conn->prepare('SELECT * FROM phase WHERE nom= :nom_phase AND num_projet=:id');
$requete->bindValue(':nom_phase',$nom_phase) ;
$requete->bindValue(':id',$row_pr[0]) ;
$requete->execute();
$row_Ph = $requete->fetch();


$requete = $conn->prepare('DELETE FROM taches WHERE (id_phase= :idp) ');
$requete->bindValue(':idp',$row_Ph[0]) ;
$requete->execute();

$requete = $conn->prepare('DELETE FROM phase WHERE id= :idp');
$requete->bindValue(':idp',$row_Ph[0]) ;

$requete->execute();





//modifier l'etat du projet dans la table des diagrammes
$requete = $conn->prepare('SELECT * FROM phase WHERE num_projet= :id');
$requete->bindValue(':id',$row_pr[0]) ;
$requete->execute();
$phases= $requete->fetchAll();

$etat_coll=0.00;
$etat_cpt=0.00;
if (count($phases)==1)
{

  $requete = $conn->prepare('SELECT * FROM phase WHERE num_projet= :id');
  $requete->bindValue(':id',$row_pr[0]) ;
  $requete->execute();
  $phases= $requete->fetch();
  $etat_coll=floatval($phases[3]);

}
elseif (count($phases)>1)
{

  for ($i=0; $i <count($phases) ; $i++)
   {
     $etat_cpt=floatval($etat_cpt)+floatval($phases[$i][3]);
   }
   $etat_coll=floatval ( $etat_cpt/count($phases));


}
else
{
  //aucune phase restante
  $etat_coll=0;//par default
}

$requete = $conn->prepare('INSERT INTO diagrammes VALUES(null,:id_projet,:etat,:date_m)');
$requete->bindValue(':id_projet',$row_pr[0]) ;
$requete->bindValue(':date_m',$date_m);
$requete->bindValue(':etat',$etat_coll);
$requete->execute();




//insertion de l'action dans l'historique du projet
  $requete = $conn->prepare('INSERT INTO historique VALUES(null,:id_user,:id_projet,:date_m,:type, :action)');
  $requete->bindValue(':id_user',$user) ;
  $requete->bindValue(':id_projet',$row_pr[0]) ;
  $requete->bindValue(':date_m',$date_m);
  $requete->bindValue(':type',$type_user);
  $requete->bindValue(':action',$action);
  $requete->execute();


 ?>
