<button id="open-midia" class="no-display" type="button" data-toggle="modal"
        data-target="#midia-modal"></button>
<div id="midia-modal" class="modal fade">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h5 class="modal-title">Mídias e arquivos</h5>
            </div>

            <div class="modal-body">
                <div class="html5-uploader"></div>
                <div class="loaded mt-30 row">
                    <div class="col-lg-7 col-md-7 col-md-12 col-sm-12">
                        <div class="row loads"></div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-md-12 col-sm-12">
                                <button data-from="10" class="load-more btn btn-primary">
                                    <i class="icon-reload-alt"></i>
                                    Carregar mais arquivos
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-lg-offset-1 col-md-4 col-md-offset-1 col-md-12 col-sm-12">
                        <h5 class="modal-title">Informações do arquivo</h5>
                        <div class="info">
                            <span class="help-block">Nenhum arquivo selecionado.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" data-dismiss="modal">
                    <i class="icon-cross2"></i>
                    Apenas sair
                </button>
                <button class="btn btn-primary"><i class="icon-checkmark3"></i> Adicionar objecto(s) selecionado(s)
                </button>
            </div>
        </div>
    </div>
</div>

<div id="midia-cropper" class="modal fade">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-close-modal="#midia-cropper">×</button>
                <h5 class="modal-title">Editor de imagens</h5>
            </div>

            <div class="modal-body">
                <div class="mt-30 row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="configs">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <div class="btn-group btn-group-justified demo-cropper-ratio" data-toggle="buttons">
                                            <label class="btn btn-light">
                                                <input type="radio" class="sr-only aspect" name="aspect" value="1.7777777777777777">
                                                16:9
                                            </label>
                                            <label class="btn btn-light">
                                                <input type="radio" class="sr-only aspect" name="aspect" value="1.3333333333333333">
                                                4:3
                                            </label>
                                            <label class="btn btn-light">
                                                <input type="radio" class="sr-only aspect" name="aspect" value="1">
                                                1:1
                                            </label>
                                            <label class="btn btn-light">
                                                <input type="radio" class="sr-only aspect" name="aspect" value="0.6666666666666666">
                                                2:3
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="btn-group btn-group-justified demo-cropper-ratio" data-toggle="buttons">
                                            <label class="btn btn-light">
                                                <input type="radio" class="sr-only sizes" name="aspect" value="1200x628">
                                                1200 x 628
                                            </label>
                                            <label class="btn btn-light">
                                                <input type="radio" class="sr-only sizes" name="aspect" value="500x500">
                                                500 x 500
                                            </label>
                                            <label class="btn btn-light">
                                                <input type="radio" class="sr-only sizes" name="aspect" value="800x600">
                                                800x600
                                            </label>
                                            <label class="btn btn-light">
                                                <input type="radio" class="sr-only sizes" name="aspect" value="1000x400">
                                                1000x400
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <button type="button" class="minus btn btn-primary mr-10">-</button>
                                            </div>
                                            <div class="col-lg-3">
                                                <button type="button"  class="btn btn-primary">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div id="the-cropper"></div>
                    </div>
                </div>

                <hr>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" data-close-modal="#midia-cropper">
                    <i class="icon-enter3"></i>
                    Sair sem editar
                </button>
                <button id="render-update-midia" class="btn btn-primary"><i class="icon-checkmark3"></i> Sair e editar
                </button>
            </div>
        </div>
    </div>
</div>

<div id="delete-midia-modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-close-modal="#delete-midia-modal">×</button>
                <h5 class="modal-title">Deletar arquivo</h5>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger alert-styled-left text-slate-800 content-group">
                    <span class="text-semibold">Deletar arquivo</span> Essa ação é irreversível
                    <button type="button" class="close" data-dismiss="alert">Ã—</button>
                </div>
                <p>Caso esse arquivo esteja uso em algum outro objeto do sistema, tais obejto passarão a apontar
                    para um arquivo inexitente, o que resultara em possíveis quebras no layout do lado do cliente
                    (front-end). </p>
                <hr>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-close-modal="#delete-midia-modal">
                    <i class="icon-enter3"></i>
                    Sair sem deletar
                </button>
                <button id="render-delete-midia" class="btn btn-danger"><i class="icon-cross"></i> Estou ciente das
                    consequências
                </button>
            </div>
        </div>
    </div>
</div>