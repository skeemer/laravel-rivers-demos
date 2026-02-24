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

export const riversBridge = merge({}, base, {attrs: { body: { stroke: '#ba0e0e', originalStroke: '#ba0e0e' }}})
export const riversFork = merge({}, base, {attrs: { body: { stroke: '#e4d52c', originalStroke: '#e4d52c' }}})
export const riversLaunch = merge({}, base, {attrs: { body: { stroke: '#31a828', originalStroke: '#31a828' }}})
export const riversRapid = merge({}, base, {attrs: { body: { stroke: '#466bc9', originalStroke: '#466bc9' }}})
