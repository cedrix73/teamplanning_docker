<?php

require_once ABS_CLASSES_PATH.'DbInterface.php';

/**
 * @name DbPdp
 * @author cvonfelten
 * Classe gérant le driver Pdo et interface les méthodes de DBInterface
 */

class DbPdo implements DbInterface 
{

	private $_noMsg;
	private $_stmt;


    public function setLog($bln) {
        $this->_noMsg = $bln;
    }

    public function getLog() {
        return $this->_noMsg;
	}
	
	
    /**
     * Etablit une connexion à un serveur de base de données et retourne un identifiant de connexion
     * L'identifiant est positif en cas de succès, FALSE sinon.
     * On pourrait se connecter avec un utilisateur lambda
     */
	public function connect($conInfos, $no_msg = 0)
	{
		$host = $conInfos['host'];
		$dbname = $conInfos['dbase'];
		$dbh=$dsn='';
		$this->_noMsg = $no_msg;
		$this->_stmt = false;
		try {
			$dsn = "mysql:host=$host;dbname=$dbname";
			$dbh = new PDO($dsn, $conInfos['username'], $conInfos['password']);
			$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo 'Failed: ' . $e->getMessage();
		}
		return $dbh;
	}
	



	/**
	 * @name: execQuery
	 * @description: Execute la requete SQL $query et renvoie  le resultSet
	 * pour être interprétée ultérieurement par fetchRow ou fetchArray.
	 * 
	 * @param ressource $link: instance renvoiée lors de la connexion PDO.
	 * @param string $query: chaine SQL
	 * @return array $resultSet : resultat de l'execution
	 */
	public function execQuery($link, $query) {
		$resultSet = $link->query($query);
		return $resultSet;
	}

	/**
	 * @name: execPreparedQuery
	 * @description: il s'agit d'un prpared Statement: Prépare et execute 
	 * la requete SQL $query et renvoie  le resultSet pour être interprétée 
	 * ultérieurement par fetchRow ou fetchArray. Si on passe des arguments 
	 * dans la requête, ils doivent être passés dans le tableau clé-valeur 
	 * $args avec comme format ":nomDeLaVariable" => valeurDeLaVariable.
	 * Important ! La requête doit être de la forme :
	 * '.. WHERE author.last_name = :prenom AND author.name = :nom'
	 * 
	 * @param ressource $link: instance renvoiée lors de la connexion PDO.
	 * @param string $query: chaine SQL
	 * @param boolean $again: Si true, le même statement est réexecuté avec de
	 *                de nouveaux arguments; $query peut être vide.
	 * @return mixed $stmt : retourne le statement de la requête.
	 */
	public function execPreparedQuery($link, $query, $args=null, $again) {
		if(!$again) {
			$this->_stmt = false;
		}
		try {
			if($again || $this->_stmt = $link->prepare($query)){
				if($args !== null) {
					foreach ($args as $varName => $varValue) {
						$this->_stmt->bindParam($varName, $varValue);
					}
				}
				$this->_stmt->execute();
			}
		} catch (PDOException $e) {
			if($this->_noMsg !== false) {
				echo 'Problème lors de l\'execution de la requête: ' . $e->getMessage();
			}
			
		}
		return $this->_stmt;
	}




	/**
	 * @name:          numRows
	 * @description:   Retourne le nombre de lignes qui sera retournées ultérieurement par
	 *                 fetchRow ou fetchArray.
	 * @param          array $resultSet: resultat de l'execution de la requête soit par execQuery(), 
	 *                 soit par execPreparedQuery.
	 */
	public function numRows($resultSet) {
		return $resultSet->rowCount();
	}

	/**
	 * @name:          fetchRow
	 * @description:   Retourne un tableau énuméré clé-valeur  dont les indexes de clé sont numériques 
	 *                 et correspondent dans l'ordre des colonnes spécifiées en clause SELECT.
	 *                 Retourne FALSE s'il n'existe pas de résultat.
	 * @param          array $resultSet: resultat de l'execution de la requête soit par execQuery(), 
	 *                 soit par execPreparedQuery.
	 */
	public function fetchRow($resultSet) 
	{
		$results = false;
		try {
			$results = $resultSet->fetchAll(PDO::FETCH_NUM);
		} catch (PDOException $e) {
			if($this->_noMsg !== false) {
				echo 'Problème lors du traitement du résultat de la requête ' 
			   . ' en tableau numérique: ' . $e->getMessage();
			}
			
		}
		return $results;
	}
	
	/**
	 * @name:          fetchArray
	 * @description:   Retourne un tableau associatif dont la clé correspond aux nom colonnes 
	 *                 spécifiées en clause SELECT. Retourne FALSE s'il n'existe pas de résultat. 
	 * @param          array $resultSet: resultat de l'execution de la requête soit par execQuery(), 
	 *                 soit par execPreparedQuery.
	 */
	public function fetchArray($resultSet) 
	{
		$results = false;
		try {
			$results = $resultSet->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			if($this->_noMsg !== false) {
				echo 'Problème lors du traitement du résultat de la requête ' 
			   . ' en tableau associatif: ' . $e->getMessage();
			}
			
		}
		return $results;
	}

	
	
	public function escapeString($link, $arg)
	{
		return $link->quote($arg);
	}

	public function getTableDatas($link, $query)
	{
		return $this->execQuery($link, $query);
	}
}

?>
