import template from './ai-layout-sidebar-item.html.twig';
import AiLayoutApiService from '../AiLayout.api.service';

const keepProperties = {
    page: [
        'type',
        'sections',
    ],
    section: [
        'blocks'
    ],
    block: [
        'type',
        'slots',
    ],
    slot: [
        'type',
        'slot',
        'config',
    ],
};

const placeholders = [
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
    'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
];

Shopware.Component.register('ai-layout-sidebar-item', {
    template,
    inject: [
        AiLayoutApiService.name,
    ],
    data() {
        return {
            loading: false,
            prompt: 'foo',
        }
    },
    computed: {
        cmsPageState() {
            return Shopware.State.get('cmsPageState');
        },
        cmsPage() {
            return this.cmsPageState.currentPage;
        },
        randomPlaceholder() {
            return placeholders[Math.floor(Math.random() * placeholders.length)];
        },
    },
    methods: {
        async onSubmit() {
            try {
                this.loading = true;

                const response = await this.AiLayoutApiService.prompt(this.prompt, this.getPageData());

                console.log(response)
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        getPageData() {
            const data = Shopware.Utils.object.deepCopyObject(this.cmsPage);

            data.sections.forEach((section) => {
                section.blocks.forEach((block) => {
                    block.slots.forEach((slot) => {
                        this.removeProperties(keepProperties.slot, slot);
                    });

                    this.removeProperties(keepProperties.block, block);
                });

                this.removeProperties(keepProperties.section, section);
            });

            this.removeProperties(keepProperties.page, data);

            return data;
        },

        removeProperties(whitelist: string[], data: object) {
            Object.keys(data).forEach((key) => {
                if (whitelist.indexOf(key) >= 0) {
                    return;
                }

                delete data[key];
            });
        },
    },
});
