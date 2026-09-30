<!-- Modal -->
<div class="modal fade" id="modal-contact{{$cont->id}}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Delete Confirmation</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <form class="delete" action="{{ route('contactus.destroy',$cont->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-success" type=button data-dismiss="modal">No</button>
                    <button class="btn btn-danger " type=submit>Yes</button>
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- Modal -->