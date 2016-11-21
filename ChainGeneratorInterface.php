<?php

namespace Zemd\Component\Menu;

interface ChainGeneratorInterface
{
  /**
   * @param NodeInterface $baseNode
   * @return NodeInterface|NodeInterface[]
   */
  public function getChain(NodeInterface $baseNode = null);
}
