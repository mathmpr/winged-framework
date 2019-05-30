<?php

namespace Winged\Database\Drivers;

/**
 * Interface EloquentInterface
 *
 * @package Winged\Database\Drivers
 */
interface EloquentInterface
{
    /**
     * @return $this
     */
    public function parseQuery();

    /**
     * @return $this
     */
    public function parseJoin();

    /**
     * @return $this
     */
    public function parseWhere();

    /**
     * @return $this
     */
    public function parseGroup();

    /**
     * @return $this
     */
    public function parseHaving();

    /**
     * @return $this
     */
    public function parseOrder();

    /**
     * @return $this
     */
    public function parseSet();

    /**
     * @return $this
     */
    public function parseValues();

    /**
     * @return $this
     */
    public function parseLimit();


    /**
     * @return $this
     */
    public function parseFrom();

    /**
     * @return $this
     */
    public function parseSelect();

    /**
     * @return $this
     */
    public function build();

}