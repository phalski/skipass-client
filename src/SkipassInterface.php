<?php


namespace Phalski\Skipass;

use Phalski\Skipass\Model\PassId;

/**
 * Interface SkipassInterface
 * @package Phalski\Skipass
 */
interface SkipassInterface
{
    /**
     * @param string $projectId
     * @param PassId $id
     * @param string|null $locale
     * @throws ContextNotFoundException
     * @return Context
     */
    public function findContextByPassId(string $projectId, PassId $id, string $locale = null): Context;

    /**
     * @param Context $context
     * @param int $dayId
     * @throws UnexpectedContentException
     * @return mixed
     */
    public function findDayById(Context $context, int $dayId);

    /**
     * @param Context $context
     * @param int $offset
     * @param int $limit
     * @return mixed
     */
    public function findDays(Context $context, int $offset = 0, int $limit = 50);
}