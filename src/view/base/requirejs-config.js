var config = {
    paths: {
        Livewire: 'Magewirephp_Magewire/js/livewire'
    },
    deps: [
        'Magewirephp_MagewireBackend/js/magewire/plugin/intersect',
        'Magewirephp_MagewireBackend/js/magewire-init',
        'Magewirephp_MagewireBackend/js/magewire-loader',
        'Magewirephp_MagewireBackend/js/magewire-loader/loader-listener'
    ],
    map: {
        '*': {
            magewire: 'Magewirephp_MagewireBackend/js/magewire',
            magewireEvent: 'Magewirephp_MagewireBackend/js/magewire-event'
        }
    }
}
