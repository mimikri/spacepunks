<?php

/**
 *  Spacepunks
 *   2moons by Jan-Otto Kröpke 2009-2016
 *   Spacepunks by mimikri 2023
 *
 * For the full copyright and license information, please view the LICENSE
 *
 * @package Spacepunks
 * @author mimikri
 * @copyright 2009 Lucky
 * @copyright 2016 Jan-Otto Kröpke <slaver7@gmail.com>
 * @copyright 2023 mimikri
 * @licence MIT
 * @version 0.0.1
 * @link https://github.com/mimikri/spacepunks
 */


class ShowTechtreePage extends AbstractGamePage
{
    public static $requireModule = MODULE_TECHTREE;

    function __construct()
    {
        parent::__construct();
    }

    function show(): void
    {
        global $resource, $requeriments, $reslist, $USER, $PLANET;

        $elementIDs		= array_merge(
            [0],
            $reslist['build'],
            [100],
            $reslist['tech'],
            [200],
            $reslist['fleet'],
            [400],
            $reslist['defense'],
            [500],
            $reslist['missile'],
            [600],
            $reslist['officier']
        );

        $techTreeList = [];

        foreach($elementIDs as $elementId)
        {
            if(!isset($resource[$elementId]))
            {
                $techTreeList[$elementId]	= $elementId;
            }
            else
            {
                $requirementsList	= [];
                if(isset($requeriments[$elementId]))
                {
                    foreach($requeriments[$elementId] as $requireID => $RedCount)
                    {
                        $requirementsList[$requireID]	= ['count' => $RedCount, 'own'   => $PLANET[$resource[$requireID]] ?? $USER[$resource[$requireID]]];
                    }
                }

                $techTreeList[$elementId]	= $requirementsList;
            }
        }

        $this->assign(['TechTreeList'		=> $techTreeList]);

        $this->display('page.techTree.default.tpl');
    }
}
