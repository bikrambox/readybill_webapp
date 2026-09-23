<x-coreweb::modal id="showMessageModal" title="Alert" subtitle="" modalPosition="modal-dialog-centered"
    titlePosition="text-center" subtitlePosition="text-center" modalHeaderPadding="" closeButtonHide="d-none"
    subTitleTextColor="text-danger">
    <div class="text-center">
        <h5 class="text-danger text-center errorMessage">{{ __('common.API Key Not Found') }}</h5>
        <a class="btn btn-success" href="{{locale_route('subscription')}}" role="button">Renew Subscription</a>
    </div>
</x-coreweb::modal>

<script>
</script>