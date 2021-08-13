<tr>
    <th scope="row">{{ $article->id }}</th>
    <td>{{ $article->file_name }}</td>
    <td>{{ $article->date_modified->format('M-d-Y') }}</td>
    <td>{{ $article->size() }}</td>
    <td>{{ $article->import_duration() }}</td>
    <td>{{ $article->original_record_count }}</td>
    <td>{{ $article->extracted_record_count }}</td>
    <td>{{ $article->new_record_count }}</td>
    <td>{{ $article->updated_record_count }}</td>
    <td>

        <div class="row">
            <div class="col">

                <!-- Modal -->
                <div wire:ignore.self class="modal fade" id="exampleModal-{{ $article->id }}" aria-labelledby="exampleModalLabel-{{ $article->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel-{{ $article->id }}">
                                    {{ $to_import_type_title }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning" role="alert">
                                    {{ $to_import_type_warning }}
                                </div>
                                File : <h3>{{ $article->file_name }}</h3>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button wire:click="import_json" data-bs-dismiss="modal" type="button" class="btn btn-primary">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button wire:loading.remove wire:target="import_json,export" wire:key="btn_import_{{ $article->id }}" wire:click="set_to_read_json_type('import')" type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#exampleModal-{{ $article->id }}">
                    Import
                </button>
                <button wire:loading.remove wire:target="import_json,export" wire:key="btn_force_import_{{ $article->id }}" wire:click="set_to_read_json_type('force')" type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal-{{ $article->id }}">
                    Force Import
                </button>

                <span wire:loading wire:target="import_json">
                    <div class="d-flex align-items-center">
                        <strong>Importing...</strong>
                        <div class="spinner-border ms-auto" role="status" aria-hidden="true"></div>
                    </div>
                </span>

                <span wire:loading wire:target="export">
                    <div class="d-flex align-items-center">
                        <strong>Exporting...</strong>
                        <div class="spinner-border ms-auto" role="status" aria-hidden="true"></div>
                    </div>
                </span>

            </div>
            <div class="col">
                <div class="input-group input-group-sm">
                    @if($export_qty_category==1)
                    <select class="form-select" id="export_qty" name="export_qty" wire:model="export_qty">
                        <option value="1">1-10k</option>
                        <option value="2">10k-20k</option>
                        <option value="3">20k-30k</option>
                        <option value="4">30k-40k</option>
                        <option value="5">40k-50k</option>
                        <option value="6">50k-60k</option>
                        <option value="7">60k-70k</option>
                        <option value="8">70k-80k</option>
                        <option value="9">80k-90k</option>
                        <option value="10">90k-100k</option>
                    </select>

                    @elseif($export_qty_category==2)
                    <select class="form-select" id="export_qty" name="export_qty" wire:model="export_qty">
                        <option value="1">1-20k</option>
                        <option value="2">20k-40k</option>
                        <option value="3">40k-60k</option>
                        <option value="4">60k-80k</option>
                        <option value="5">80k-100k</option>
                    </select>
                    @endif
                    <!-- Modal -->
                    <div wire:ignore.self class="modal fade" id="modal-export-article-{{ $article->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-light">
                                    <h5 class="modal-title" id="modal-export-article-{{ $article->id }}">
                                        Export Options
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                                </div>
                                <div class="modal-body">
                                    <div class="p-2 text-center">
                                        <strong>{{ strtoupper($article->file_name) }}</strong>
                                    </div>

                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th colspan="3">Languages</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($export_languages as $lang)
                                            <tr>
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" wire:click="lang_clicked({{ $lang->id }},$event.target.checked)" value="{{ $lang->id }}" {{ $lang->selected ? 'checked' : '' }}>
                                                    </div>
                                                </th>
                                                <td>{{ $lang->code }}</td>
                                                <td>{{ $lang->language }} </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button wire:click="export" data-bs-dismiss="modal" type="button" class="btn btn-primary">OK</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <button wire:key="btn_export_{{ $article->id }}" class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#modal-export-article-{{ $article->id }}">
                        <i class="bi bi-arrow-down"></i> Export
                    </button>

                </div>

            </div>
        </div>





    </td>
    <td>
        <input type="radio" id="all-{{ $article->id }}" name="sel_typ-{{ $article->id }}" value="1" wire:model="sel_type">
        <label for="all-{{ $article->id }}">All</label>
        <input type="radio" id="new-{{ $article->id }}" name="sel_typ-{{ $article->id }}" value="2" wire:model="sel_type">
        <label for="new-{{ $article->id }}">Updated</label>


    </td>
</tr>