import {portsIn, portsOut} from "./ports.js";
import merge from 'lodash.merge';

const base = {
    size: { width: 180, height: 50 },
    attrs: {
        body: {
            stroke: '#c94657',
            rx: 10,
            ry: 10,
        },
    },
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    },
}

export const riversFork = merge({}, base, {attrs: { body: { stroke: '#7a077f', originalStroke: '#7a077f' }}})
export const riversLaunch = merge({}, base, {attrs: { body: { stroke: '#466bc9', originalStroke: '#466bc9' }}})
export const riversRapid = merge({}, base, {attrs: { body: { stroke: '#c94656', originalStroke: '#c94656' }}})
