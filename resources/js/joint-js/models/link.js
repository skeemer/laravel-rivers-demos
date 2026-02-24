import {shapes} from '@joint/core'

export class Link extends shapes.standard.Link {
    constructor(attributes = null, options = null) {
        super(attributes, options)

        this.router('manhattan', {
            startDirections: ['right'],
            endDirections: ['left'],
            padding: 10,
        })

        this.connector('straight', {cornerType: 'cubic'})
    }
}
