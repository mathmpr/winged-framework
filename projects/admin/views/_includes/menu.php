<?php

use Winged\Winged;

/**
 * @var $this \Winged\Controller\Controller
 */
?>
<div class="sidebar sidebar-main">
    <div class="sidebar-content">
        <!-- User menu -->
        <div class="sidebar-user">
            <div class="category-content">
                <div class="media">
                    <a href="usuarios/update/<?= Login::current()->primaryKey() ?>" class="media-left">
                        <span class="circle _circle"><?= Login::getLetters() ?></span>
                    </a>
                    <div class="media-body">
                        <span class="media-heading text-semibold"><?= Login::current()->nome ?></span>
                        <div class="text-size-mini text-muted">
                            <i class="<?= Login::currentIsAdm() ? 'icon-magic-wand2' : 'icon-quil2' ?> text-size-small"></i>
                            &nbsp;<?= Login::currentIsAdm() ? 'Admin' : 'Normal' ?>
                        </div>
                    </div>
                    <div class="media-right media-middle">
                        <ul class="icons-list">
                            <li>
                                <a href="usuarios/update/<?= Login::current()->primaryKey() ?>"><i
                                        class="icon-cog3"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- /user menu -->

        <!-- Main navigation -->
        <div class="sidebar-category sidebar-category-visible">
            <div class="category-content no-padding">
                <ul class="navigation navigation-main navigation-accordion">
                    <!-- Main -->
                    <li class="navigation-header"><span>Principal</span> <i class="icon-menu" title="Main pages"></i>
                    </li>
                    <li <?= $this->active_page_group === 'default' ? 'class="active"' : '' ?>><a href="default"><i class="icon-equalizer4"></i> <span>SEO Config</span></a>
                    </li>

                    <li <?= $this->active_page_group === 'seo_pages' ? 'class="active"' : '' ?>><a href="seo-pages"><i class="icon-equalizer4"></i> <span>SEO Pages</span></a>
                    </li>
                    <?php

                    $acitveMenus = new UsuariosPermissoesMenu();
                    $acitveMenus = $acitveMenus->select()
                        ->from(['SPM' => UsuariosPermissoesMenu::tableName()])
                        ->where(ELOQUENT_EQUAL, ['SPM.id_usuario' => Login::current()->primaryKey()])
                        ->execute();
                    $arr = [];
                    if($acitveMenus){
                        foreach ($acitveMenus as $acitveMenu){
                            $arr[] = $acitveMenu->id_menu;
                        }
                    }
                    $activeMenus= $arr;

                    $activeSubmenus = new UsuariosPermissoesSubmenu();
                    $activeSubmenus = $activeSubmenus->select()
                        ->from(['SPS' => UsuariosPermissoesSubmenu::tableName()])
                        ->where(ELOQUENT_EQUAL, ['SPS.id_usuario' => Login::current()->primaryKey()])
                        ->execute();
                    $arr = [];
                    if($activeSubmenus){
                        foreach ($activeSubmenus as $activeSubmenu){
                            $arr[] = $activeSubmenu->id_submenu;
                        }
                    }
                    $activeSubmenus = $arr;

                    $menus = (new Menu())->findAll();
                    if ($menus) {
                        /**
                         * @var $menu Menu
                         */
                        foreach ($menus as $menu) {
                            if (in_array($menu->primaryKey(), $activeMenus) || Login::currentIsAdm()) {
                                $submenus = $menu->getSubmenu();
                                if ($submenus) {
                                    ?>
                                    <li>
                                        <a href="javascript:;"><i class="<?= $menu->icone ?>"></i>
                                            <span><?= $menu->nome ?></span></a>
                                        <ul>
                                            <?php
                                            foreach ($submenus as $submenu) {
                                                if (in_array($submenu->primaryKey(), $activeSubmenus) || Login::currentIsAdm()) {
                                                    $in_array = explode('/', $submenu->link);
                                                    ?>
                                                    <li <?= $menu->pagina === $this->active_page_group && in_array(Winged::$page_surname, $in_array) ? 'class="active"' : '' ?>><a href="<?= $submenu->link ?>"><?= $submenu->nome ?></a></li>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </ul>
                                    </li>
                                    <?php
                                } else {
                                    ?>
                                    <li <?= $menu->pagina === $this->active_page_group ? 'class="active"' : '' ?>>
                                        <a href="<?= $menu->link ?>">
                                            <i class="<?= $menu->icone ?>"></i>
                                            <span><?= $menu->nome ?></span>
                                        </a>
                                    </li>
                                    <?php
                                }
                            }
                        }
                    }
                    ?>

                    <!--li>
                        <a href="#"><i class="icon-stack2"></i> <span>Page layouts</span></a>
                        <ul>
                            <li><a href="layout_navbar_fixed.html">Fixed navbar</a></li>
                        </ul>
                    </li-->
                </ul>
            </div>
        </div>
        <!-- /main navigation -->
    </div>
</div>