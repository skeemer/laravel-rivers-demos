import {dia, shapes} from '@joint/core'
import {riversBridge, riversFork, riversLaunch, riversRapid} from './models/cells.js'
import {Link} from './models/link.js'
import merge from 'lodash.merge';

export default function jointJs(containerId) {
    const namespace = shapes;

    const container = document.getElementById(containerId)
    const flyContainer = document.getElementById(`${containerId}-fly`)
    const paletteContainer = document.getElementById(`${containerId}-palette`)

    const graph = new dia.Graph({}, { cellNamespace: namespace })
    const flyGraph = new dia.Graph({}, { cellNamespace: namespace })
    const paletteGraph = new dia.Graph({}, { cellNamespace: namespace })

    const paper = new dia.Paper({
        el: container,
        model: graph,
        cellViewNamespace: namespace,

        width: '100%',
        height: '100%',
        background: { color: '#FAFBFB' },

        defaultLink: () => new Link(),
        linkPinning: false,
        snapLinks: { radius: 50 },

        validateConnection: function(cellViewS, magnetS, cellViewT, magnetT, end, linkView) {
            // Prevent linking from input ports
            if (magnetS && magnetS.getAttribute('port-group') === 'in') return false
            // Prevent linking from output ports to input ports within one element
            if (cellViewS === cellViewT) return false
            // Prevent linking to already linked ports
            if (graph.getConnectedLinks(cellViewT.model, { inbound: true }).length > 0) return false
            // Prevent linking to output ports
            return magnetT && magnetT.getAttribute('port-group') === 'in'
        },
    })

    const flyPaper = new dia.Paper({
        el: flyContainer,
        model: flyGraph,
        width: '100',
        height: '100%',
        cellViewNamespace: namespace
    })

    const palettePaper = new dia.Paper({
        el: paletteContainer,
        model: paletteGraph,
        width: '200',
        height: '100%',
        background: { color: '#ffffff' },
        cellViewNamespace: namespace,
        interactive: false,
    })

    /**
     * Add re-centering
     */
    function recenter() {
        const containerBox = container.getBoundingClientRect()
        const graphBox = graph.getBBox()

        if (graphBox) {
            paper.translate(
                containerBox.width / 2 - graphBox.x - graphBox.width / 2,
                containerBox.height / 2 - graphBox.y - graphBox.height / 2
            )
        }
    }

    /**
     * Add dragging to flypaper
     */
    function moveFlyListener(event) {
        const cell = flyGraph.getCell('drag-cell')
        cell.position(cell.position().x + event.movementX, cell.position().y + event.movementY)
    }

    /**
     * Add panning by dragging
     */
    // function moveListener(event) {
    //     const current = paper.translate();
    //     paper.translate(current.tx + event.movementX, current.ty + event.movementY);
    // }
    // paper.on('blank:pointerdown', () => document.addEventListener('mousemove', moveListener));
    // paper.on('blank:pointerup', () => document.removeEventListener('mousemove', moveListener));

    /**
     * Add panning by scrolling
     */
    let sensitivity = 7;
    paper.on('blank:mousewheel', (custom, a, b, delta) => {
        const current = paper.translate();
        if (custom.shiftKey) {
            paper.translate(current.tx + delta * sensitivity, current.ty);
        } else if (custom.ctrlKey) {
            event.preventDefault();
            paper.scale(paper.scale().sx + delta * sensitivity / 500);
        } else {
            paper.translate(current.tx, current.ty + delta * sensitivity);
        }
    });

    /**
     * Add selection handling
     */
    let selection = null

    function clearSelection() {
        if (selection) {
            selection.attr({
                'body': {
                    'stroke': selection.attr('body').originalStroke ?? '#466bc9',
                    'strokeWidth': 2,
                }
            })
        }
    }

    const setupSelection = (callback) => {
        paper.on('cell:pointerclick', (view) => {
            clearSelection()
            selection = view.model
            if (selection.attributes.type === 'standard.Link') return
            selection.attr({
                'body': {
                    'stroke': '#C94A46',
                    'strokeWidth': 4,
                }
            })
            callback(selection.id)
        })

        paper.on('blank:pointerclick', () => {
            clearSelection()
            selection = null
            callback(null)
        })
    }

    paper.on('link:pointerdblclick', (view) => {
        this.$wire.removeLink(view.model.id);
        graph.removeCell(view.model);
    })

    paper.on('link:connect', (linkView, a, b, d) => {
        this.$wire.addLink(linkView.model.source().id, linkView.model.source().port,linkView.model.target().id)
        graph.removeCell(linkView.model);
    })

    function reRoute() {
        graph.getLinks().forEach(link => paper.findViewByModel(link).render())
    }

    // Make sure the dragging element is on top
    // paper.on('element:pointerdown', (cellView) => cellView.model.toFront())

    // Make sure routes are re-rendered after element movement
    paper.on('element:pointerup', () => reRoute())

    return {
        draggingNew: false,
        selectedId: null,
        init() {
            const aThis = this

            setupSelection(id => {
                this.selectedId = id
                this.$wire.set('selectedId', id)
            })
            setTimeout(() => recenter(), 50)

            this.addCell({id: 'launch', x: 10, y: 10, text: 'Launch', type: 'launch'}, paletteGraph)
            this.addCell({id: 'rapid', x: 10, y: 70, text: 'Rapid', type: 'rapid'}, paletteGraph)
            this.addCell({id: 'fork', x: 10, y: 130, text: 'Fork', type: 'fork'}, paletteGraph)
            this.addCell({id: 'bridge', x: 10, y: 190, text: 'Bridge', type: 'bridge'}, paletteGraph)

            function releaseNewDrag(event) {
                const x = event.clientX
                const y = event.clientY

                document.removeEventListener('mousemove', moveFlyListener)
                document.removeEventListener('mouseup', releaseNewDrag)
                aThis.draggingNew = false

                const dragCell = flyGraph.getCell('drag-cell')
                const current = paper.translate()
                const box = dragCell.getBBox()

                // Make sure the pointer is not over the palette
                const paletteRect = paletteContainer.getBoundingClientRect()
                if (
                    (x < paletteRect.x || x > paletteRect.x + paletteRect.width) ||
                    (y < paletteRect.y || y > paletteRect.y + paletteRect.height)
                ) {
                    aThis.$wire.newCell(dragCell.attr('riversType'), box.x - current.tx, box.y - current.ty)
                }

                flyGraph.removeCell(dragCell)

                reRoute()
            }

            flyPaper.on('blank:pointerup', () => document.removeEventListener('mousemove', moveFlyListener));
            palettePaper.on('cell:pointerdown', (cellView) => {
                this.draggingNew = true

                document.addEventListener('mousemove', moveFlyListener)
                document.addEventListener('mouseup', releaseNewDrag)
                const bounding = paletteContainer.getBoundingClientRect()
                const box = cellView.getBBox()
                this.addCell({
                    id: 'drag-cell',
                    x: box.x + bounding.x,
                    y: box.y + bounding.y,
                    text: cellView.model.attr('label').text,
                    type: cellView.model.id,
                }, flyGraph)
            });
        },
        addCell(cell, toGraph = null) {
            const targetGraph = toGraph ?? graph

            let model = null;
            switch (cell.type) {
                case 'bridge':
                    model = this.createCell(cell, riversBridge)
                    break
                case 'fork':
                    model = this.createCell(cell, riversFork)
                    break
                case 'launch':
                    model = this.createCell(cell, riversLaunch)
                    break
                case 'rapid':
                    model = this.createCell(cell, riversRapid)
                    break
                default:
                    return
            }

            if (targetGraph.getCell(cell.id) && cell.type === 'fork') {
                return
            }
            model.addTo(targetGraph)

            if (! toGraph) {
                if (cell.type !== 'fork') {
                    model.addPorts([{group: 'in'}, {group: 'out', id: `${cell.id}-out`}])
                } else if (cell.type === 'fork') {
                    model.listenTo(
                        model,
                        'change:ports',
                        () => model.resize(
                            model.size().width,
                            model.prop('ports/items').filter(item => item.group === 'out').length * 50,
                        )
                    )
                    model.addPorts([
                        {group: 'in'},
                        ...cell.ports.map((port, index) => ({
                            group: 'out',
                            id: port,
                            attrs: {label: {text: (index+1).toString()}},
                        })),
                        {group: 'out', id: `${cell.id}-else`, attrs: {label: {text: 'E'}}},
                    ])
                }
                reRoute()
            }

            return model
        },
        createCell(cell, baseShape) {
            return new shapes.standard.Rectangle(merge({
                id: cell.id,
                position: { x: cell.x, y: cell.y },
                attrs: {
                    label: { text: cell.text ?? cell.id },
                    riversType: cell.type,
                }
            }, baseShape))
        },
        addLink(id, source, target, port) {
            new Link({
                id,
                source: { id: source, port: port },
                target: { id: target },
            }).addTo(graph).toBack()
        },
        addToGraph(cell) {
            cell.addTo(graph)
        },
        getCell(id, targetGraph = null) {
            return (targetGraph ?? graph).getCell(id)
        }
    }
}
