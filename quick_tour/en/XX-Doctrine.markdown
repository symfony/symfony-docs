Symfony2 Quick Tour: The Doctrine Bundle
========================================

If your project relies on a database in one way or another, feel free to
choose any tool you want. You can even use an ORM like Doctrine or Propel if
you want to abstract the database. But in this section, we will keep things
simple and use the Doctrine DBAL, a thin layer on top of PDO, to connect to
the database.

Enable the `DoctrineBundle` and configure your connection in `config.yml` by
adding the following lines:

    # hello/config/config.yml
    doctrine.dbal:
      driver:   PDOMySql # can be any of OCI8, PDOMsSql, PDOMySql, PDOOracle, PDOPgSql, or PDOSqlite
      dbname:   your_db_name
      user:     root
      password: your_password # or null if there is none

That's all there is to it. You can now use a connection object to interact
with the database from any action:

    [php]
    public function showAction($id)
    {
      $stmt = $this->getDatabaseConnection()->execute('SELECT * FROM product WHERE id = ?', array($id));

      if (!$product = $stmt->fetch())
      {
        throw new NotFoundHttpException('The product does not exist.');
      }

      return $this->render(...);
    }

The `$this->getDatabaseConnection()` expression returns an object that works
like the PDO one, based on the configuration of `config.yml`.
