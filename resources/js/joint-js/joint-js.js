import { dia, shapes } from '@joint/core'
import {rapidBase, rapidLaunch} from './models/cells.js'
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

    paper.on('link:connect', (linkView) => {
        this.$wire.addLink(linkView.model.getSourceCell().id, linkView.model.getTargetCell().id)
        graph.removeCell(linkView.model);
    })

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

            this.addLaunch({id: 'launch', x: 10, y: 10, text: 'Launch'}, paletteGraph)
            this.addRapid({id: 'rapid', x: 10, y: 70, text: 'Rapid'}, paletteGraph)

            function releaseNewDrag() {
                document.removeEventListener('mousemove', moveFlyListener)
                document.removeEventListener('mouseup', releaseNewDrag)
                aThis.draggingNew = false

                const dragCell = flyGraph.getCell('drag-cell')
                const current = paper.translate()
                const box = dragCell.getBBox()

                // TODO make sure mouse is not over palette

                aThis.$wire.newCell(dragCell.attr('riversType'), box.x - current.tx, box.y - current.ty)

                flyGraph.removeCell(dragCell)
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
            let model = null;
            switch (cell.type) {
                case 'launch':
                    model = this.addLaunch(cell, toGraph)
                    break
                case 'rapid':
                    model = this.addRapid(cell, toGraph)
                    break
                default:
                    return
            }

            if (cell.id !== 'drag-cell' && cell.type !== 'fork') {
                model.addPorts([{group: 'in'}, {group: 'out'}])
            } else if (cell.type === 'fork') {
                model.addPorts([{group: 'in'}, {group: 'out'}, {group: 'out'}])
            }

            return model
        },
        addLaunch(cell, toGraph = null) {
            return new shapes.standard.Rectangle(merge({
                id: cell.id,
                position: { x: cell.x, y: cell.y },
                attrs: {
                    label: { text: cell.text ?? cell.id },
                    riversType: cell.type,
                }
            }, rapidLaunch)).addTo(toGraph ?? graph)
        },
        addRapid(cell, toGraph = null) {
            const model = new shapes.standard.Rectangle(merge({
                id: cell.id,
                position: { x: cell.x, y: cell.y },
                attrs: {
                    label: { text: cell.text ?? cell.id },
                    riversType: cell.type,
                }
            }, rapidBase))

            return model.addTo(toGraph ?? graph)
        },
        addLink(id, source, target) {
            return new Link({id, source: graph.getCell(source), target: graph.getCell(target)}).addTo(graph)
        },
        addToGraph(cell) {
            cell.addTo(graph)
        },
    }
}
