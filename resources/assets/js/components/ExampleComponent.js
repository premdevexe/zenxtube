/**
 * Vanilla JavaScript Component
 * Replaces legacy Vue component with pure HTML5 & DOM API
 */
export default class ExampleComponent {
    constructor(element) {
        this.element = typeof element === 'string' ? document.querySelector(element) : element;
        this.init();
    }

    init() {
        if (!this.element) return;
        this.render();
        console.log('Zen X Tube vanilla component mounted.');
    }

    render() {
        this.element.innerHTML = `
            <div class="container my-3">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card card-default shadow-sm border-0">
                            <div class="card-header bg-white font-weight-bold">Zen X Component</div>
                            <div class="card-body">
                                Clean Vanilla HTML5, CSS3, and JavaScript component.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}
