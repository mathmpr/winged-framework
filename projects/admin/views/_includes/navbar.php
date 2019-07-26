<?php

/**
 * @var $this Controller
 */
?>
<div class="navbar navbar-inverse">
    <div class="navbar-header">
        <a class="navbar-brand" href="default"><div class="circle _circle"><?= Login::getLetters() ?></div></a>
        <ul class="nav navbar-nav visible-xs-block">
            <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
            <li><a class="sidebar-mobile-main-toggle"><i class="icon-paragraph-justify3"></i></a></li>
        </ul>
    </div>
    <div class="navbar-collapse collapse" id="navbar-mobile">
        <ul class="nav navbar-nav">
            <li><a class="sidebar-control sidebar-main-toggle hidden-xs"><i class="icon-paragraph-justify3"></i></a></li>
        </ul>
        <p class="navbar-text"><span class="label bg-success">Online</span></p>
        <ul class="nav navbar-nav navbar-right">
            <li class="dropdown dropdown-user">
                <a class="dropdown-toggle" data-toggle="dropdown">
                    <span class="circle _circle"><?= Login::getLetters() ?></span>
                    <span><?= Login::current()->nome ?></span>
                    <i class="caret"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <?php
                    if(Login::currentIsAdm()){
                        ?>
                        <li><a href="usuarios"><i class="icon-user-plus"></i> Usuários</a></li>
                        <li class="divider"></li>
                        <?php
                    }
                    ?>
                    <li><a href="usuarios/update/<?= Login::current()->primaryKey() ?>"><i class="icon-cog5"></i> Minha conta</a></li>
                    <li><a href="login/logout"><i class="icon-switch2"></i> Sair</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>