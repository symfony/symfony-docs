.. index::
   single: Doctrine; ORM Console Commands
   single: CLI; Doctrine ORM

Console Commands
================

The Doctrine2 ORM integration offers several console commands under the ``doctrine``
namespace. To view a list of the commands you can run the console without any arguments
or options:

    $ php app/console
    ...

    doctrine
      :ensure-production-settings  Verify that Doctrine is properly configured for a production environment.
      :schema-tool                 Processes the schema and either apply it directly on EntityManager or generate the SQL output.
    doctrine:cache
      :clear-metadata              Clear all metadata cache for a entity manager.
      :clear-query                 Clear all query cache for a entity manager.
      :clear-result                Clear result cache for a entity manager.
    doctrine:data
      :load                        Load data fixtures to your database.
    doctrine:database
      :create                      Create the configured databases.
      :drop                        Drop the configured databases.
    doctrine:generate
      :entities                    Generate entity classes and method stubs from your mapping information.
      :entity                      Generate a new Doctrine entity inside a bundle.
      :proxies                     Generates proxy classes for entity classes.
      :repositories                Generate repository classes from your mapping information.
    doctrine:mapping
      :convert                     Convert mapping information between supported formats.
      :convert-d1-schema           Convert a Doctrine1 schema to Doctrine2 mapping files.
      :import                      Import mapping information from an existing database.
    doctrine:query
      :dql                         Executes arbitrary DQL directly from the command line.
      :sql                         Executes arbitrary SQL directly from the command line.
    doctrine:schema
      :create                      Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output.
      :drop                        Processes the schema and either drop the database schema of EntityManager Storage Connection or generate the SQL output.
      :update                      Processes the schema and either update the database schema of EntityManager Storage Connection or generate the SQL output.

    ...
