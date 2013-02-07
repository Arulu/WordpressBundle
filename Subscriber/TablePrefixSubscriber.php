<?php

/**
 * Prefixing WordPress table
 *
 * Provide a Table Prefix option for the bundle's entities.
 */

namespace Hypebeast\WordpressBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\EventSubscriber;

class TablePrefixSubscriber implements EventSubscriber
{
	protected $prefix = '';

	public function __construct($prefix)
	{
		$this->prefix = (string) $prefix;
	}

	public function getSubscribedEvents()
	{
		return array('loadClassMetadata');
	}

	public function loadClassMetadata(LoadClassMetadataEventArgs $args)
	{
		// do not apply prefix if not a WordpressBundle Entitiy
		if($args->getClassMetadata()->namespace !== "Hypebeast\WordpressBundle\Entity") {
			return;
		}

		$classMetadata = $args->getClassMetadata();
		$conf = $args->getEntityManager()->getConfiguration();

		$connection = $conf->getConnectionFor('Hypebeast\WordpressBundle\Entity');
		$classMetadata->setConnection($connection);
		$platform = $connection->getDatabasePlatform();

		$tableName = $args->getEntityManager()->getConfiguration()->getQuoteStrategy()->getTableName($classMetadata, $platform);

		$tableName = preg_replace('/' . $connection->getDatabase() . './', '', $tableName);

		$prefix = $this->getTablePrefix($args);

		$classMetadata->setTableName($prefix . $tableName);
	}

	private function getTablePrefix(LoadClassMetadataEventArgs $args)
	{
		$prefix = $this->prefix;

		// append blog id to prefix, if needed.
		if( method_exists($args->getEntityManager()->getMetadataFactory(), 'getBlogId') &&
			$args->getClassMetadata()->name !== "Hypebeast\WordpressBundle\Entity\User" &&
			$args->getClassMetadata()->name !== "Hypebeast\WordpressBundle\Entity\UserMeta") {
			$prefix .= $args->getEntityManager()->getMetadataFactory()->getBlogId().'_';
		}

		return $prefix;
	}
}