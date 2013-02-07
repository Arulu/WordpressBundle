<?php

/*
 * Copyright (c) 2012 Arulu Inversiones SL
 * Todos los derechos reservados
 */

namespace Hypebeast\WordpressBundle\Repository;

use Arulu\Reusable\EntityRepository;

class PostRepository extends EntityRepository
{
	public function getLastPosts($limit = null)
	{
		return $this->createQuery('Select p FROM HypebeastWordpressBundle:Post p WHERE p.status = :status ORDER BY p.date DESC')
			->setParameter('status', 'publish')
			->setMaxResults($limit)
			->getResult();
	}
}